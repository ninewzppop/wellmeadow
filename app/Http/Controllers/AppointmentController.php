<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Outpatient;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Pharmaceutical;
use App\Models\Room;
use App\Models\Stf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Appointment::with(['patient', 'consultant', 'room'])
            ->orderBy('ApptDate', 'desc')
            ->orderBy('ApptTime', 'desc');

        // Role scope: doctors see only their own appointments (overrides ?consultant=).
        $user = $request->user();
        if ($user->isClinician() && $user->stf_no !== null) {
            $query->where('Consult_Stf_No', $user->stf_no);
        } else {
            $ids = $user->accessiblePatientIds();
            if ($ids !== null) {
                $query->whereIn('Pt_No', $ids);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Appt_No', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('Pt_No', 'like', "%{$search}%")
                            ->orWhere('FirstName', 'like', "%{$search}%")
                            ->orWhere('LastName', 'like', "%{$search}%");
                    })
                    ->orWhereHas('consultant', function ($cq) use ($search) {
                        $cq->where('FirstName', 'like', "%{$search}%")
                            ->orWhere('LastName', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->where('ApptDate', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('ApptDate', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('consultant')) {
            $query->where('Consult_Stf_No', $request->consultant);
        }

        if ($request->filled('room')) {
            $query->where('Room_No', $request->room);
        }

        $appointments = $query->paginate(15)->withQueryString();

        $consultants = Stf::orderBy('LastName')->orderBy('FirstName')->get();
        $rooms = Room::orderBy('RoomName')->get();
        $statuses = Appointment::statusLabels();

        return view('appointments.index', compact('appointments', 'consultants', 'rooms', 'statuses'));
    }

    public function create(): View
    {
        $patients = Patient::orderBy('LastName')->orderBy('FirstName')->get();
        $consultants = Stf::orderBy('LastName')->orderBy('FirstName')->get();
        $rooms = Room::orderBy('RoomName')->get();

        return view('appointments.form', [
            'appointment' => new Appointment,
            'patients' => $patients,
            'consultants' => $consultants,
            'rooms' => $rooms,
            'statuses' => $this->formStatuses(null),
            'allergyDrugs' => Pharmaceutical::orderBy('Name')->get(),
            'allergyStaff' => Stf::orderBy('LastName')->orderBy('FirstName')->get(),
            'severities' => AllergyController::SEVERITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAppointment($request);
        $allergyData = $this->validateInlineAllergy($request);
        $data['Appt_No'] = $this->nextApptNo();

        DB::transaction(function () use ($data, $allergyData) {
            Appointment::create($data);

            if ($allergyData !== null) {
                $allergyData['Allergy_No'] = $this->generateId('PatientAllergy', 'Allergy_No', 'AL');
                PatientAllergy::create($allergyData);
            }
        });

        if ($queueContext = $this->queueContext($request)) {
            return redirect()->route('rooms.show', $queueContext)
                ->with('status', __('Created appointment :no.', ['no' => $data['Appt_No']]));
        }

        return redirect()->route('appointments.index')
            ->with('status', __('Created appointment :no.', ['no' => $data['Appt_No']]));
    }

    public function show(Appointment $appointment): View|RedirectResponse
    {
        if (Gate::denies('view', $appointment)) {
            return redirect()->route('forbidden');
        }

        $appointment->load(['patient', 'consultant', 'room', 'outpatient']);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment): View|RedirectResponse
    {
        if (Gate::denies('update', $appointment)) {
            return redirect()->route('forbidden');
        }

        $patients = Patient::orderBy('LastName')->orderBy('FirstName')->get();
        $consultants = Stf::orderBy('LastName')->orderBy('FirstName')->get();
        $rooms = Room::orderBy('RoomName')->get();

        return view('appointments.form', [
            'appointment' => $appointment,
            'patients' => $patients,
            'consultants' => $consultants,
            'rooms' => $rooms,
            'statuses' => $this->formStatuses($appointment),
            'allergyDrugs' => Pharmaceutical::orderBy('Name')->get(),
            'allergyStaff' => Stf::orderBy('LastName')->orderBy('FirstName')->get(),
            'severities' => AllergyController::SEVERITIES,
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        if (Gate::denies('update', $appointment)) {
            return redirect()->route('forbidden');
        }

        $data = $this->validateAppointment($request, $appointment);
        $allergyData = $this->validateInlineAllergy($request);

        DB::transaction(function () use ($appointment, $data, $allergyData) {
            $appointment->update($data);

            if ($allergyData !== null) {
                $allergyData['Allergy_No'] = $this->generateId('PatientAllergy', 'Allergy_No', 'AL');
                PatientAllergy::create($allergyData);
            }
        });

        return redirect()->route('appointments.index')
            ->with('status', __('Updated appointment :no.', ['no' => $appointment->Appt_No]));
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        if (Gate::denies('delete', $appointment)) {
            return redirect()->route('forbidden');
        }

        $no = $appointment->Appt_No;

        DB::transaction(function () use ($appointment) {
            // Outpatient is a 1:1 extension of the appointment (FK without
            // cascade); remove it so the appointment itself can be deleted.
            Outpatient::where('Appt_out_No', $appointment->Appt_No)->delete();

            $appointment->delete();
        });

        return redirect()->route('appointments.index')
            ->with('status', __('Deleted appointment :no.', ['no' => $no]));
    }

    private function formStatuses(?Appointment $appointment): array
    {
        $selectable = [
            Appointment::STATUS_WAITING,
            Appointment::STATUS_SCHEDULED,
            'cancelled',
            'no-show',
        ];

        // Lifecycle states (in consultation / completed-*) come only from
        // queue actions; editing such a record locks its status.
        if ($appointment && ! in_array($appointment->status, $selectable, true)) {
            return [$appointment->status];
        }

        return $selectable;
    }

    private function queueContext(Request $request): ?array
    {
        $room = (string) $request->input('ctx_room', '');
        $date = (string) $request->input('ctx_date', '');

        if ($room === '' || ! Room::whereKey($room)->exists()) {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m) !== 1 || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }

        return ['room' => $room, 'date' => $date];
    }

    protected function validateAppointment(Request $request, ?Appointment $existing = null): array
    {
        $allowedStatuses = $this->formStatuses($existing);

        return $request->validate([
            'Pt_No' => ['required', 'exists:Patient,Pt_No'],
            'Consult_Stf_No' => ['required', 'exists:Stf,Stf_No'],
            'ApptDate' => ['required', 'date'],
            'ApptTime' => ['required', 'date_format:H:i'],
            'Room_No' => ['required', 'exists:Room,Room_No'],
            'status' => ['required', 'string', 'max:20', Rule::in($allowedStatuses)],
        ]);
    }

    protected function nextApptNo(): string
    {
        $max = Appointment::where('Appt_No', 'like', 'A%')
            ->pluck('Appt_No')
            ->map(fn (string $apptNo) => (int) substr($apptNo, 1))
            ->max();

        return 'A'.($max + 1);
    }

    protected function validateInlineAllergy(Request $request): ?array
    {
        $hasInput = $request->filled('allergy_Reaction')
            || $request->filled('allergy_Drug_No')
            || $request->filled('allergy_Severity')
            || $request->filled('allergy_DiagDate');

        if (! $hasInput) {
            return null;
        }

        $validated = $request->validate([
            'allergy_Drug_No' => ['nullable', 'exists:Pharmaceutical,Drug_No'],
            'allergy_Reaction' => ['required', 'string', 'max:150'],
            'allergy_Severity' => ['required', 'string', 'max:15', 'in:'.implode(',', AllergyController::SEVERITIES)],
            'allergy_DiagDate' => ['required', 'date'],
            'allergy_Rec_Stf_No' => ['nullable', 'exists:Stf,Stf_No'],
        ]);

        $allergyName = null;
        if (! empty($validated['allergy_Drug_No'])) {
            $drug = Pharmaceutical::find($validated['allergy_Drug_No']);
            $allergyName = $drug?->Name;
        }

        return [
            'Pt_No' => $request->input('Pt_No'),
            'Drug_No' => $validated['allergy_Drug_No'] ?? null,
            'Allergy_Name' => $allergyName,
            'Reaction' => $validated['allergy_Reaction'],
            'Severity' => $validated['allergy_Severity'],
            'DiagDate' => $validated['allergy_DiagDate'],
            'Rec_Stf_No' => $validated['allergy_Rec_Stf_No'] ?? null,
        ];
    }
}
