<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Outpatient;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Stf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Appointment::with(['patient', 'consultant', 'room'])
            ->orderBy('ApptDate', 'desc')
            ->orderBy('ApptTime', 'desc');

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
            'nextApptNo' => $this->nextApptNo(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAppointment($request);
        $data['Appt_No'] = $this->nextApptNo();

        Appointment::create($data);

        if ($queueContext = $this->queueContext($request)) {
            return redirect()->route('rooms.show', $queueContext)
                ->with('status', __('Created appointment :no.', ['no' => $data['Appt_No']]));
        }

        return redirect()->route('appointments.index')
            ->with('status', __('Created appointment :no.', ['no' => $data['Appt_No']]));
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'consultant', 'room', 'outpatient']);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment): View
    {
        $patients = Patient::orderBy('LastName')->orderBy('FirstName')->get();
        $consultants = Stf::orderBy('LastName')->orderBy('FirstName')->get();
        $rooms = Room::orderBy('RoomName')->get();

        return view('appointments.form', [
            'appointment' => $appointment,
            'patients' => $patients,
            'consultants' => $consultants,
            'rooms' => $rooms,
            'statuses' => $this->formStatuses($appointment),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $this->validateAppointment($request, $appointment);

        $appointment->update($data);

        return redirect()->route('appointments.index')
            ->with('status', __('Updated appointment :no.', ['no' => $appointment->Appt_No]));
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
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
}
