<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Stf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $statuses = ['scheduled', 'completed', 'cancelled', 'no-show'];

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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAppointment($request);

        $appointment = Appointment::create($data);

        return redirect()->route('appointments.index')
            ->with('status', __('Created appointment :no.', ['no' => $appointment->Appt_No]));
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

        return view('appointments.form', compact('appointment', 'patients', 'consultants', 'rooms'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $this->validateAppointment($request, $appointment->Appt_No);

        $appointment->update($data);

        return redirect()->route('appointments.index')
            ->with('status', __('Updated appointment :no.', ['no' => $appointment->Appt_No]));
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $no = $appointment->Appt_No;

        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('status', __('Deleted appointment :no.', ['no' => $no]));
    }

    protected function validateAppointment(Request $request, ?string $ignoreApptNo = null): array
    {
        return $request->validate([
            'Appt_No' => [
                'required',
                'string',
                'max:10',
                $ignoreApptNo ? 'unique:Appointment,Appt_No,'.$ignoreApptNo.',Appt_No' : 'unique:Appointment,Appt_No',
            ],
            'Pt_No' => ['required', 'exists:Patient,Pt_No'],
            'Consult_Stf_No' => ['required', 'exists:Stf,Stf_No'],
            'ApptDate' => ['required', 'date'],
            'ApptTime' => ['required', 'date_format:H:i'],
            'Room_No' => ['required', 'exists:Room,Room_No'],
            'status' => ['required', 'string', 'max:20'],
        ]);
    }
}
