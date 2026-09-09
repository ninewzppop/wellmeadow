<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MedicationOrder;
use App\Models\Medications;
use App\Models\Patient;
use App\Models\Pharmaceutical;
use App\Models\Room;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MedicationOrderController extends Controller
{
    public function index(): View
    {
        $query = MedicationOrder::with(['patient', 'prescriber', 'items.drug'])
            ->where('status', MedicationOrder::STATUS_PENDING);

        // Role scope: clinicians see their own prescriptions, ward roles their ward's patients.
        $user = auth()->user();
        if ($user->isClinician() && $user->stf_no !== null) {
            $query->where('Stf_No', $user->stf_no);
        } else {
            $ids = $user->accessiblePatientIds();
            if ($ids !== null) {
                $query->whereIn('Pt_No', $ids);
            }
        }

        $orders = $query->orderBy('OrderedAt')
            ->orderBy('Order_No')
            ->get();

        return view('medication-orders.index', compact('orders'));
    }

    public function show(MedicationOrder $order): View|RedirectResponse
    {
        if (Gate::denies('view', $order)) {
            return redirect()->route('forbidden');
        }

        $order->load(['items.drug', 'patient.allergies.drug', 'prescriber', 'appointment']);

        $conflicts = $this->conflictsFor(
            $order->patient,
            $order->items->map(fn ($item) => $item->drug)->filter(),
        );

        return view('medication-orders.show', [
            'order' => $order,
            'conflicts' => $conflicts,
        ]);
    }

    public function store(Request $request, Room $room, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->Room_No === $room->Room_No, 404);

        if (Gate::denies('create', MedicationOrder::class)) {
            return redirect()->route('forbidden');
        }

        if ($appointment->status !== Appointment::STATUS_IN_CONSULTATION) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('Only appointments currently in consultation can be completed.'),
            ]);
        }

        if (! $appointment->patient) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('This appointment has no linked patient.'),
            ]);
        }

        // drop empty rows where no drug selected (left by JS template / user added extra rows)
        $request->merge([
            'drugs' => array_values(array_filter($request->input('drugs', []), fn ($r) => ! empty($r['Drug_No'] ?? null))),
        ]);

        $data = $request->validate([
            'drugs' => ['required', 'array', 'min:1'],
            'drugs.*.Drug_No' => ['required', 'exists:Pharmaceutical,Drug_No'],
            'drugs.*.UnitsPerDay' => ['required', 'integer', 'min:1'],
            'drugs.*.AdminMethod' => ['required', 'string', 'max:30'],
            'drugs.*.StartDate' => ['required', 'date'],
            'drugs.*.FinishDate' => ['required', 'date', 'after_or_equal:drugs.*.StartDate'],
        ]);

        $drugs = Pharmaceutical::whereIn('Drug_No', collect($data['drugs'])->pluck('Drug_No'))->get()->keyBy('Drug_No');
        $conflicts = $this->conflictsFor($appointment->patient, collect($data['drugs'])->pluck('Drug_No')->map(fn ($no) => $drugs[$no]));

        if ($conflicts->isNotEmpty() && ! $request->boolean('override_allergy')) {
            return $this->backToQueue($request, $room)
                ->withErrors(['drugs' => __('Allergy conflict: :detail. Tick the confirmation box to dispense anyway.', [
                    'detail' => $conflicts->map(fn ($a) => $a->Allergy_Name ?: $a->drug?->Name ?: $a->Drug_No)->unique()->implode(', '),
                ])])
                ->withInput();
        }

        $orderNo = DB::transaction(function () use ($appointment, $data) {
            $order = MedicationOrder::create([
                'Order_No' => MedicationOrder::nextNo(),
                'Pt_No' => $appointment->Pt_No,
                'Stf_No' => $appointment->Consult_Stf_No,
                'Appt_No' => $appointment->Appt_No,
                'status' => MedicationOrder::STATUS_PENDING,
            ]);

            foreach ($data['drugs'] as $row) {
                $order->items()->create([
                    'Drug_No' => $row['Drug_No'],
                    'UnitsPerDay' => (int) $row['UnitsPerDay'],
                    'AdminMethod' => $row['AdminMethod'],
                    'StartDate' => $row['StartDate'],
                    'FinishDate' => $row['FinishDate'],
                ]);
            }

            $appointment->update(['status' => Appointment::STATUS_COMPLETED_MEDICATION]);

            return $order->Order_No;
        });

        return $this->backToQueue($request, $room)->with(
            'status',
            __('Sent medication order :no to the dispensing queue.', ['no' => $orderNo]),
        );
    }

    public function confirm(Request $request, MedicationOrder $order): RedirectResponse
    {
        if (Gate::denies('dispense', $order)) {
            return redirect()->route('forbidden');
        }

        if ($order->status !== MedicationOrder::STATUS_PENDING) {
            return redirect()->route('medications.index')
                ->withErrors(['queue' => __('This order is no longer pending.')]);
        }

        $order->loadMissing(['items.drug', 'patient']);

        $needs = [];
        foreach ($order->items as $item) {
            $days = $item->StartDate->diffInDays($item->FinishDate) + 1;
            $needs[$item->getKey()] = $item->UnitsPerDay * $days;
        }

        try {
            DB::transaction(function () use ($order, $needs) {
                $drugNos = $order->items->pluck('Drug_No')->unique()->filter()->values();
                $stocks = Pharmaceutical::whereIn('Drug_No', $drugNos)->lockForUpdate()->get()->keyBy('Drug_No');

                foreach ($order->items as $item) {
                    $need = $needs[$item->getKey()];
                    $stock = $stocks[$item->Drug_No] ?? null;
                    $available = $stock?->QtyInStock ?? 0;
                    if ($available < $need) {
                        $drugName = $stock?->Name ?? $item->drug?->Name ?? $item->Drug_No;
                        throw ValidationException::withMessages([
                            'queue' => __('Cannot dispense :drug — only :stock left, need :need.', [
                                'drug' => $drugName, 'stock' => $available, 'need' => $need,
                            ]),
                        ]);
                    }
                }

                foreach ($order->items as $item) {
                    $need = $needs[$item->getKey()];
                    $stock = $stocks[$item->Drug_No];

                    $stock->update(['QtyInStock' => ($stock->QtyInStock ?? 0) - $need]);

                    StockMovement::create([
                        'Drug_No' => $item->Drug_No,
                        'QtyChange' => -$need,
                        'Note' => __('Dispensed for :order — :patient — :drug ×:need', [
                            'order' => $order->Order_No, 'patient' => $order->Pt_No, 'drug' => $stock->Name ?? $item->Drug_No, 'need' => $need,
                        ]),
                        'Moved_By' => auth()->id(),
                        'MoveDate' => now(),
                    ]);

                    Medications::create([
                        'Med_No' => Medications::nextNo(),
                        'Pt_No' => $order->Pt_No,
                        'Stf_No' => $order->Stf_No,
                        'Drug_No' => $item->Drug_No,
                        'UnitsPerDay' => $item->UnitsPerDay,
                        'AdminMethod' => $item->AdminMethod,
                        'StartDate' => $item->StartDate,
                        'FinishDate' => $item->FinishDate,
                    ]);
                }

                $now = now();
                $order->update([
                    'status' => MedicationOrder::STATUS_DISPENSED,
                    'PaidAt' => $now,
                    'DispensedAt' => $now,
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect()->route('medications.index')
            ->with('status', __('Dispensed medication for :name.', [
                'name' => $order->patient?->full_name ?? $order->Order_No,
            ]));
    }

    public function cancel(Request $request, MedicationOrder $order): RedirectResponse
    {
        if (Gate::denies('dispense', $order)) {
            return redirect()->route('forbidden');
        }

        if ($order->status !== MedicationOrder::STATUS_PENDING) {
            return redirect()->route('medications.index')
                ->withErrors(['queue' => __('This order is no longer pending.')]);
        }

        $data = $request->validate([
            'CancelReason' => ['required', 'string', 'max:255'],
        ]);

        $order->update([
            'status' => MedicationOrder::STATUS_CANCELLED,
            'CancelledAt' => now(),
            'CancelReason' => $data['CancelReason'],
        ]);

        return redirect()->route('medications.index')
            ->with('status', __('Cancelled medication order :no.', ['no' => $order->Order_No]));
    }

    private function conflictsFor(?Patient $patient, Collection $drugs): Collection
    {
        if (! $patient || $drugs->isEmpty()) {
            return collect();
        }

        return $patient->allergies->filter(function ($allergy) use ($drugs) {
            return $drugs->contains(fn (Pharmaceutical $drug) => ($allergy->Drug_No !== null && $allergy->Drug_No === $drug->Drug_No)
                || ($allergy->Allergy_Name !== null && $drug->Name !== null && strcasecmp($allergy->Allergy_Name, $drug->Name) === 0));
        })->values();
    }

    private function backToQueue(Request $request, Room $room): RedirectResponse
    {
        $raw = (string) $request->input('date', '');
        $valid = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw) === 1;

        return redirect()->route('rooms.show', array_filter([
            'room' => $room->Room_No,
            'date' => $valid ? $raw : null,
        ], fn ($v) => $v !== null));
    }
}
