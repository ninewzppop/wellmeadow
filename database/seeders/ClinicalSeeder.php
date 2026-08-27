<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClinicalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LocalDr')->updateOrInsert(['Clinic_No' => 'LD01'], [
            'FirstName' => 'David', 'LastName' => 'Miller', 'Address' => '5 High Street, Bristol', 'TelNo' => '0117 555 0200',
        ]);
        DB::table('LocalDr')->updateOrInsert(['Clinic_No' => 'LD02'], [
            'FirstName' => 'Ruth', 'LastName' => 'Green', 'Address' => '22 King Road, Bath', 'TelNo' => '01225 555 0220',
        ]);

        DB::table('Room')->updateOrInsert(['Room_No' => 'R001'], ['RoomName' => 'Consulting Room 1', 'Location' => 'Block A, Floor 1']);
        DB::table('Room')->updateOrInsert(['Room_No' => 'R002'], ['RoomName' => 'Treatment Room', 'Location' => 'Block B, Floor 2']);

        DB::table('Patient')->updateOrInsert(['Pt_No' => 'PT001'], [
            'FirstName' => 'Olivia', 'LastName' => 'Brown', 'Address' => '3 Rose Lane, Bristol',
            'TelNo' => '0117 555 0301', 'DOB' => '1985-02-14', 'Sex' => 'F', 'MaritalStat' => 'Married',
            'DateReg' => '2026-01-10', 'Clinic_No' => 'LD01',
        ]);
        DB::table('Patient')->updateOrInsert(['Pt_No' => 'PT002'], [
            'FirstName' => 'George', 'LastName' => 'Hill', 'Address' => '9 Station Road, Bath',
            'TelNo' => '01225 555 0302', 'DOB' => '1952-08-30', 'Sex' => 'M', 'MaritalStat' => 'Widowed',
            'DateReg' => '2026-02-22', 'Clinic_No' => 'LD02',
        ]);
        DB::table('Patient')->updateOrInsert(['Pt_No' => 'PT003'], [
            'FirstName' => 'Mary', 'LastName' => 'Jones', 'Address' => '12 Orchard Way, Bristol',
            'TelNo' => '0117 555 0303', 'DOB' => '1990-11-05', 'Sex' => 'F', 'MaritalStat' => 'Single',
            'DateReg' => '2026-03-08', 'Clinic_No' => 'LD01',
        ]);
        DB::table('Patient')->updateOrInsert(['Pt_No' => 'PT004'], [
            'FirstName' => 'Leo', 'LastName' => 'Wong', 'Address' => '4 Bridge Street, Bath',
            'TelNo' => '01225 555 0304', 'DOB' => '1978-04-21', 'Sex' => 'M', 'MaritalStat' => 'Married',
            'DateReg' => '2026-04-02', 'Clinic_No' => 'LD02',
        ]);

        DB::table('NextOfKin')->updateOrInsert(['NOK_No' => 'NOK01'], [
            'Pt_No' => 'PT001', 'FirstName' => 'Mark', 'LastName' => 'Brown', 'Relationship' => 'Husband',
            'Address' => '3 Rose Lane, Bristol', 'TelNo' => '0117 555 0310',
        ]);
        DB::table('NextOfKin')->updateOrInsert(['NOK_No' => 'NOK02'], [
            'Pt_No' => 'PT002', 'FirstName' => 'Sue', 'LastName' => 'Hill', 'Relationship' => 'Daughter',
            'Address' => '14 Park Close, Bath', 'TelNo' => '01225 555 0320',
        ]);

        DB::table('Appointment')->updateOrInsert(['Appt_No' => 'A001'], [
            'Pt_No' => 'PT001', 'Consult_Stf_No' => 'S1002', 'ApptDate' => '2026-08-20', 'ApptTime' => '09:30:00', 'Room_No' => 'R001',
        ]);
        DB::table('Appointment')->updateOrInsert(['Appt_No' => 'A002'], [
            'Pt_No' => 'PT002', 'Consult_Stf_No' => 'S1002', 'ApptDate' => '2026-08-21', 'ApptTime' => '11:00:00', 'Room_No' => 'R002',
        ]);

        // Same-day demo queue for the Rooms pages (ADR-0004).
        $today = Carbon::today()->toDateString();

        DB::table('Appointment')->updateOrInsert(['Appt_No' => 'A010'], [
            'Pt_No' => 'PT003', 'Consult_Stf_No' => 'S1002', 'ApptDate' => $today, 'ApptTime' => '16:00:00',
            'Room_No' => 'R001', 'status' => 'waiting list',
        ]);
        DB::table('Appointment')->updateOrInsert(['Appt_No' => 'A011'], [
            'Pt_No' => 'PT001', 'Consult_Stf_No' => 'S1002', 'ApptDate' => $today, 'ApptTime' => '09:45:00',
            'Room_No' => 'R001', 'status' => 'in consultation',
        ]);
        DB::table('Appointment')->updateOrInsert(['Appt_No' => 'A012'], [
            'Pt_No' => 'PT004', 'Consult_Stf_No' => 'S1002', 'ApptDate' => $today, 'ApptTime' => '10:15:00',
            'Room_No' => 'R002', 'status' => 'scheduled',
        ]);
        DB::table('Appointment')->updateOrInsert(['Appt_No' => 'A013'], [
            'Pt_No' => 'PT002', 'Consult_Stf_No' => 'S1002', 'ApptDate' => $today, 'ApptTime' => '13:30:00',
            'Room_No' => 'R002', 'status' => 'completed-medication',
        ]);
        DB::table('Appointment')->updateOrInsert(['Appt_No' => 'A014'], [
            'Pt_No' => 'PT003', 'Consult_Stf_No' => 'S1002', 'ApptDate' => $today, 'ApptTime' => '09:30:00',
            'Room_No' => 'R001', 'status' => 'completed-waitlist',
        ]);

        DB::table('PatientAllergy')->updateOrInsert(['Allergy_No' => 'AL20'], [
            'Pt_No' => 'PT003', 'Drug_No' => 'DR01', 'Allergy_Name' => 'Paracetamol',
            'Reaction' => 'Anaphylaxis', 'Severity' => 'Severe', 'DiagDate' => '2025-06-10', 'Rec_Stf_No' => 'S1002',
        ]);

        DB::table('Outpatient')->updateOrInsert(['Appt_out_No' => 'A001'], []);

        DB::table('InPatient')->updateOrInsert(['In_Pt_No' => 'IP001'], [
            'Pt_No' => 'PT002', 'Bed_No' => '101', 'DateWaitList' => '2026-07-01',
            'ExpStayDays' => 5, 'DatePlaced' => '2026-08-01', 'DateLeave' => null, 'ActDateLeft' => null,
        ]);
    }
}
