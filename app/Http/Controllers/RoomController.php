<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\InPatient;
use App\Models\Medications;
use App\Models\Patient;
use App\Models\Pharmaceutical;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $date = $this->resolveDate($request);

        $rooms = Room::orderBy('Room_No')->get();

        $counts = Appointment::query()
            ->whereDate('ApptDate', $date->toDateString())
            ->selectRaw('Room_No, status, COUNT(*) as total')
            ->groupBy('Room_No', 'status')
            ->get()
            ->groupBy('Room_No');

        $summary = $rooms->mapWithKeys(function (Room $room) use ($counts) {
            $byStatus = optional($counts->get($room->Room_No))->pluck('total', 'status') ?? collect();

            return [$room->Room_No => [
                'waiting' => $byStatus->get(Appointment::STATUS_WAITING, 0)
                    + $byStatus->get(Appointment::STATUS_SCHEDULED, 0),
                'inConsultation' => $byStatus->get(Appointment::STATUS_IN_CONSULTATION, 0),
                'completed' => collect(Appointment::COMPLETED_STATUSES)
                    ->sum(fn (string $status) => (int) $byStatus->get($status, 0)),
            ]];
        });

        return view('rooms.index', [
            'rooms' => $rooms,
            'summary' => $summary,
            'date' => $date,
        ]);
    }

    public function show(Request $request, Room $room): View
    {
        $date = $this->resolveDate($request);

        $queue = Appointment::with(['patient.allergies.drug', 'consultant'])
            ->where('Room_No', $room->Room_No)
            ->whereDate('ApptDate', $date->toDateString())
            ->active()
            ->orderBy('ApptTime')
            ->orderBy('Appt_No')
            ->get();

        $finished = Appointment::with(['patient.allergies.drug', 'consultant'])
            ->where('Room_No', $room->Room_No)
            ->whereDate('ApptDate', $date->toDateString())
            ->completed()
            ->orderByDesc('ApptTime')
            ->orderByDesc('Appt_No')
            ->get();

        return view('rooms.show', [
            'room' => $room,
            'date' => $date,
            'queue' => $queue,
            'finished' => $finished,
            'drugs' => Pharmaceutical::orderBy('Name')->get(),
            'inConsultationNo' => $queue->firstWhere('status', Appointment::STATUS_IN_CONSULTATION)?->Appt_No,
        ]);
    }

    public function start(Request $request, Room $room, Appointment $appointment): RedirectResponse
    {
        $this->assertSameRoom($room, $appointment);

        if (! in_array($appointment->status, [Appointment::STATUS_WAITING, Appointment::STATUS_SCHEDULED], true)) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('Only waiting appointments can be started.'),
            ]);
        }

        $busy = Appointment::query()
            ->where('Room_No', $room->Room_No)
            ->where('status', Appointment::STATUS_IN_CONSULTATION)
            ->where('Appt_No', '!=', $appointment->Appt_No)
            ->exists();

        if ($busy) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('Another patient is currently in consultation in this room. Complete that visit first.'),
            ]);
        }

        $appointment->update(['status' => Appointment::STATUS_IN_CONSULTATION]);

        return $this->backToQueue($request, $room)->with(
            'status',
            __('Started consultation for :name.', ['name' => $appointment->patient?->full_name ?? $appointment->Appt_No]),
        );
    }

    public function medicate(Request $request, Room $room, Appointment $appointment): RedirectResponse
    {
        $this->assertSameRoom($room, $appointment);

        if (! $this->isInConsultation($appointment)) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('Only appointments currently in consultation can be completed.'),
            ]);
        }

        if (! $appointment->patient) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('This appointment has no linked patient.'),
            ]);
        }

        $data = $request->validate([
            'Drug_No' => ['required', 'exists:Pharmaceutical,Drug_No'],
            'UnitsPerDay' => ['required', 'integer', 'min:1'],
            'AdminMethod' => ['required', 'string', 'max:30'],
            'StartDate' => ['required', 'date'],
            'FinishDate' => ['required', 'date', 'after_or_equal:StartDate'],
        ]);

        $drug = Pharmaceutical::findOrFail($data['Drug_No']);
        $conflicts = $this->allergyConflicts($appointment->patient, $drug);

        if ($conflicts->isNotEmpty() && ! $request->boolean('override_allergy')) {
            return $this->backToQueue($request, $room)
                ->withErrors(['Drug_No' => __('Allergy conflict: :detail. Tick the confirmation box to dispense anyway.', [
                    'detail' => $conflicts->map(fn ($a) => $a->Allergy_Name ?: $a->drug?->Name ?: $a->Drug_No)->implode(', '),
                ])])
                ->withInput();
        }

        DB::transaction(function () use ($appointment, $data) {
            Medications::create([
                'Med_No' => $this->generateId('Medications', 'Med_No', 'M'),
                'Pt_No' => $appointment->Pt_No,
                'Stf_No' => $appointment->Consult_Stf_No,
                'Drug_No' => $data['Drug_No'],
                'UnitsPerDay' => $data['UnitsPerDay'],
                'AdminMethod' => $data['AdminMethod'],
                'StartDate' => $data['StartDate'],
                'FinishDate' => $data['FinishDate'],
            ]);

            $appointment->update(['status' => Appointment::STATUS_COMPLETED_MEDICATION]);
        });

        return $this->backToQueue($request, $room)->with(
            'status',
            __('Medication dispensed for :name. Visit completed.', ['name' => $appointment->patient?->full_name ?? $appointment->Appt_No]),
        );
    }

    public function admit(Request $request, Room $room, Appointment $appointment): RedirectResponse
    {
        $this->assertSameRoom($room, $appointment);

        if (! $this->isInConsultation($appointment)) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('Only appointments currently in consultation can be completed.'),
            ]);
        }

        if (! $appointment->patient) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('This appointment has no linked patient.'),
            ]);
        }

        $data = $request->validate([
            'ExpStayDays' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($appointment, $data) {
            InPatient::create([
                'In_Pt_No' => $this->generateId('InPatient', 'In_Pt_No', 'IP'),
                'Pt_No' => $appointment->Pt_No,
                'Bed_No' => null,
                'DateWaitList' => Carbon::today()->toDateString(),
                'ExpStayDays' => $data['ExpStayDays'],
                'DatePlaced' => null,
                'DateLeave' => null,
                'ActDateLeft' => null,
            ]);

            $appointment->update(['status' => Appointment::STATUS_COMPLETED_WAITLIST]);
        });

        return $this->backToQueue($request, $room)->with(
            'status',
            __(':name added to the in-patient waiting list. Visit completed.', ['name' => $appointment->patient?->full_name ?? $appointment->Appt_No]),
        );
    }

    public function complete(Request $request, Room $room, Appointment $appointment): RedirectResponse
    {
        $this->assertSameRoom($room, $appointment);

        if (! $this->isInConsultation($appointment)) {
            return $this->backToQueue($request, $room)->withErrors([
                'queue' => __('Only appointments currently in consultation can be completed.'),
            ]);
        }

        $appointment->update(['status' => Appointment::STATUS_COMPLETED]);

        return $this->backToQueue($request, $room)->with(
            'status',
            __('Visit completed for :name.', ['name' => $appointment->patient?->full_name ?? $appointment->Appt_No]),
        );
    }

    private function isInConsultation(Appointment $appointment): bool
    {
        return $appointment->status === Appointment::STATUS_IN_CONSULTATION;
    }

    private function assertSameRoom(Room $room, Appointment $appointment): void
    {
        abort_unless($appointment->Room_No === $room->Room_No, 404);
    }

    private function allergyConflicts(?Patient $patient, Pharmaceutical $drug): Collection
    {
        if (! $patient) {
            return collect();
        }

        return $patient->allergies->filter(
            fn ($allergy) => ($allergy->Drug_No !== null && $allergy->Drug_No === $drug->Drug_No)
                || ($allergy->Allergy_Name !== null && $drug->Name !== null && strcasecmp($allergy->Allergy_Name, $drug->Name) === 0)
        )->values();
    }

    private function resolveDate(Request $request): Carbon
    {
        $raw = (string) $request->query('date', '');

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();
        }

        return Carbon::today();
    }

    private function backToQueue(Request $request, Room $room): RedirectResponse
    {
        return redirect()->route('rooms.show', array_filter([
            'room' => $room->Room_No,
            'date' => $this->validDateString($request) ? $request->input('date') : null,
        ], fn ($v) => $v !== null));
    }

    private function validDateString(Request $request): bool
    {
        $raw = (string) $request->input('date', '');

        return preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m) === 1
            && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }
}
