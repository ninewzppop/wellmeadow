<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WellmeadowDbAuditTest extends TestCase
{
    use RefreshDatabase;

    /** (a) every table required by PDF B.3.1 exists */
    public function test_required_tables_exist(): void
    {
        $tables = ['Wd','Pos','LocalDr','Room','Supplier','Stf','Bed','Patient',
            'StfQual','StfPos','StfWorkExp','StfRota','Appointment','Outpatient',
            'NextOfKin','InPatient','CentralStock','Pharmaceutical','Medications',
            'Wardrequisitions','Itemrequest','Drugrequest','PatientAllergy',
            'StockMovement','MedicationOrder','MedicationOrderItem','users'];
        foreach ($tables as $t) {
            $this->assertTrue(Schema::hasTable($t), "missing table $t");
        }
    }

    /** (b) columns per PDF Data Requirements */
    public function test_columns_match_pdf_requirements(): void
    {
        $expect = [
            'Wd' => ['Wd_No','Wd_Name','Location','TotalBeds','TelExtension'],
            'Stf' => ['Stf_No','FirstName','LastName','Address','TelNo','DOB','Sex','NIN','Alloc_Wd_No'],
            'StfQual' => ['Qual_No','Stf_No','Type','QualDate','Institution'],
            'StfWorkExp' => ['WorkExp_No','Stf_No','Organization','Position','StartDate','FinishDate'],
            'StfPos' => ['StfPos_No','Stf_No','Pos_No','CurrSalary','HrsPerWk','ContractType','PaymentType'],
            'StfRota' => ['StfRota_No','Stf_No','Wd_No','WkBegin','Shift'],
            'Patient' => ['Pt_No','FirstName','LastName','Address','TelNo','DOB','Sex','MaritalStat','DateReg','Clinic_No'],
            'NextOfKin' => ['NOK_No','Pt_No','FirstName','LastName','Relationship','Address','TelNo'],
            'LocalDr' => ['Clinic_No','FirstName','LastName','Address','TelNo'],
            'Appointment' => ['Appt_No','Pt_No','Consult_Stf_No','Room_No','ApptDate','ApptTime','status'],
            'Outpatient' => ['Appt_out_No'],
            'InPatient' => ['In_Pt_No','Pt_No','Bed_No','DateWaitList','ExpStayDays','DatePlaced','DateLeave','ActDateLeft'],
            'Bed' => ['Bed_No','Wd_No','BedStatus'],
            'Medications' => ['Med_No','Pt_No','Stf_No','Drug_No','UnitsPerDay','AdminMethod','StartDate','FinishDate'],
            'CentralStock' => ['Item_No','Name','ItemType','Description','QtyInStock','ReorderLvl','CostPerUnit','Suppl_No'],
            'Pharmaceutical' => ['Drug_No','Name','Description','Dosage','AdminMethod','QtyInStock','ReorderLvl','CostPerUnit','Suppl_No','ExpiryDate'],
            'Wardrequisitions' => ['Wd_Req_No','Stf_No','Wd_No','DateOrd','DateRecv','status','Received_By'],
            'Itemrequest' => ['Wd_Req_No','Item_No','QtyReq'],
            'Drugrequest' => ['Wd_Req_No','Drug_No','QtyReq'],
            'Supplier' => ['Suppl_No','Name','Address','TelNo','FaxNo'],
            'PatientAllergy' => ['Allergy_No','Pt_No','Drug_No','Rec_Stf_No'],
        ];
        foreach ($expect as $table => $cols) {
            foreach ($cols as $c) {
                $this->assertTrue(Schema::hasColumn($table, $c), "$table missing $c");
            }
        }
        // types: dates must be date, not varchar
        $this->assertEquals('date', Schema::getColumnType('Stf', 'DOB'));
        $this->assertEquals('date', Schema::getColumnType('Patient', 'DOB'));
        $this->assertEquals('date', Schema::getColumnType('Appointment', 'ApptDate'));
    }

    /** (c) FK enforced: invalid reference must fail */
    public function test_foreign_keys_enforced(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('Stf')->insert(['Stf_No' => 'SFK01', 'Alloc_Wd_No' => 'WD-NOPE']);
    }

    public function test_fk_patient_bed_enforced(): void
    {
        try {
            DB::table('InPatient')->insert(['In_Pt_No' => 'IPFK01', 'Pt_No' => 'PT-NOPE']);
            $this->fail('expected FK violation on InPatient.Pt_No');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true);
        }
    }

    /** (d) unique constraints */
    public function test_unique_constraints(): void
    {
        DB::table('Stf')->insert(['Stf_No' => 'SUQ01', 'NIN' => 'NIN-UNIQ-01']);
        try {
            DB::table('Stf')->insert(['Stf_No' => 'SUQ02', 'NIN' => 'NIN-UNIQ-01']);
            $this->fail('duplicate NIN should fail');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true);
        }

        DB::table('Pos')->insert(['Pos_No' => 'PUQ01', 'Pos_Name' => 'Unique Position XYZ']);
        try {
            DB::table('Pos')->insert(['Pos_No' => 'PUQ02', 'Pos_Name' => 'Unique Position XYZ']);
            $this->fail('duplicate Pos_Name should fail');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true);
        }
    }

    /** (e) full workflow insert across the whole system */
    public function test_full_workflow_insert(): void
    {
        DB::table('Wd')->insert(['Wd_No' => 'WD99', 'Wd_Name' => 'Audit Ward', 'TotalBeds' => 5]);
        DB::table('Pos')->insert(['Pos_No' => 'P99', 'Pos_Name' => 'Audit Nurse']);
        DB::table('Stf')->insert(['Stf_No' => 'S9901', 'FirstName' => 'Moira', 'LastName' => 'Audit', 'NIN' => 'NIN-AUD-01', 'Alloc_Wd_No' => 'WD99']);
        DB::table('StfQual')->insert(['Qual_No' => 'Q9901', 'Stf_No' => 'S9901', 'Type' => 'BSc Nursing Studies', 'Institution' => 'Edinburgh University']);
        DB::table('StfWorkExp')->insert(['WorkExp_No' => 'E9901', 'Stf_No' => 'S9901', 'Organization' => 'Western Hospital', 'Position' => 'Staff Nurse']);
        DB::table('StfPos')->insert(['StfPos_No' => 'SP9901', 'Stf_No' => 'S9901', 'Pos_No' => 'P99', 'CurrSalary' => 18760, 'HrsPerWk' => 37.5]);
        DB::table('StfRota')->insert(['StfRota_No' => 'R9901', 'Stf_No' => 'S9901', 'Wd_No' => 'WD99', 'WkBegin' => '2004-01-09', 'Shift' => 'Early']);

        DB::table('LocalDr')->insert(['Clinic_No' => 'E102', 'FirstName' => 'Helen', 'LastName' => 'Pearson']);
        DB::table('Patient')->insert(['Pt_No' => 'P10234', 'FirstName' => 'Anne', 'LastName' => 'Phelps', 'Clinic_No' => 'E102']);
        DB::table('NextOfKin')->insert(['NOK_No' => 'N9901', 'Pt_No' => 'P10234', 'FirstName' => 'James', 'LastName' => 'Phelps', 'Relationship' => 'Son']);
        DB::table('Room')->insert(['Room_No' => 'E252', 'RoomName' => 'Exam', 'Location' => 'E Block']);
        DB::table('Appointment')->insert(['Appt_No' => 'A9901', 'Pt_No' => 'P10234', 'Consult_Stf_No' => 'S9901', 'Room_No' => 'E252', 'ApptDate' => '2004-02-01', 'ApptTime' => '10:00:00']);
        DB::table('Outpatient')->insert(['Appt_out_No' => 'A9901']);
        DB::table('Bed')->insert(['Bed_No' => 'B9901', 'Wd_No' => 'WD99', 'BedStatus' => 'Free']);
        DB::table('InPatient')->insert(['In_Pt_No' => 'IP9901', 'Pt_No' => 'P10234', 'Bed_No' => 'B9901', 'DateWaitList' => '2004-01-12', 'ExpStayDays' => 5, 'DatePlaced' => '2004-01-12']);

        DB::table('Supplier')->insert(['Suppl_No' => 'S9901', 'Name' => 'Audit Supplier']);
        DB::table('CentralStock')->insert(['Item_No' => 'IT9901', 'Name' => 'Syringe', 'ItemType' => 'surgical', 'QtyInStock' => 100, 'CostPerUnit' => 1.5, 'Suppl_No' => 'S9901']);
        DB::table('Pharmaceutical')->insert(['Drug_No' => 'DR9901', 'Name' => 'Morphine', 'Dosage' => '10mg/ml', 'AdminMethod' => 'Oral', 'QtyInStock' => 50, 'CostPerUnit' => 27.75, 'Suppl_No' => 'S9901']);
        DB::table('Medications')->insert(['Med_No' => 'MD9901', 'Pt_No' => 'P10234', 'Stf_No' => 'S9901', 'Drug_No' => 'DR9901', 'UnitsPerDay' => 50, 'AdminMethod' => 'Oral', 'StartDate' => '2004-03-24', 'FinishDate' => '2004-04-24']);
        DB::table('Wardrequisitions')->insert(['Wd_Req_No' => 'WR9901', 'Stf_No' => 'S9901', 'Wd_No' => 'WD99', 'DateOrd' => '2004-02-15', 'status' => 'Pending']);
        DB::table('Itemrequest')->insert(['Wd_Req_No' => 'WR9901', 'Item_No' => 'IT9901', 'QtyReq' => 10]);
        DB::table('Drugrequest')->insert(['Wd_Req_No' => 'WR9901', 'Drug_No' => 'DR9901', 'QtyReq' => 50]);
        DB::table('PatientAllergy')->insert(['Allergy_No' => 'AL9901', 'Pt_No' => 'P10234', 'Drug_No' => 'DR9901', 'Rec_Stf_No' => 'S9901', 'Reaction' => 'Rash', 'Severity' => 'Mild', 'DiagDate' => '2004-01-01']);

        $this->assertDatabaseHas('Medications', ['Med_No' => 'MD9901']);
        $this->assertDatabaseHas('InPatient', ['In_Pt_No' => 'IP9901']);
        $this->assertDatabaseHas('Drugrequest', ['Wd_Req_No' => 'WR9901']);
    }

    /** (f) cascade / set-null behaviour */
    public function test_cascade_delete_medication_order_items(): void
    {
        DB::table('Wd')->insert(['Wd_No' => 'WDC1']);
        DB::table('Stf')->insert(['Stf_No' => 'SFC1', 'Alloc_Wd_No' => 'WDC1']);
        DB::table('LocalDr')->insert(['Clinic_No' => 'EC1']);
        DB::table('Patient')->insert(['Pt_No' => 'PTC1', 'Clinic_No' => 'EC1']);
        DB::table('Room')->insert(['Room_No' => 'RC1']);
        DB::table('Appointment')->insert(['Appt_No' => 'AC1', 'Pt_No' => 'PTC1', 'Consult_Stf_No' => 'SFC1', 'Room_No' => 'RC1']);
        DB::table('Supplier')->insert(['Suppl_No' => 'SC1']);
        DB::table('Pharmaceutical')->insert(['Drug_No' => 'DRC1', 'Suppl_No' => 'SC1']);
        DB::table('MedicationOrder')->insert(['Order_No' => 'MOC1', 'Pt_No' => 'PTC1', 'Stf_No' => 'SFC1', 'Appt_No' => 'AC1']);
        DB::table('MedicationOrderItem')->insert(['Order_No' => 'MOC1', 'Drug_No' => 'DRC1', 'UnitsPerDay' => 1, 'AdminMethod' => 'Oral', 'StartDate' => '2004-01-01', 'FinishDate' => '2004-01-02']);

        DB::table('MedicationOrder')->where('Order_No', 'MOC1')->delete();
        $this->assertDatabaseMissing('MedicationOrderItem', ['Order_No' => 'MOC1']);
    }

    public function test_users_stf_no_set_null_on_staff_delete(): void
    {
        DB::table('Stf')->insert(['Stf_No' => 'SFN1']);
        $id = DB::table('users')->insertGetId(['name' => 'U', 'email' => 'u1@x.test', 'password' => 'x', 'role' => 'staff_nurse', 'stf_no' => 'SFN1']);
        DB::table('Stf')->where('Stf_No', 'SFN1')->delete();
        $this->assertDatabaseHas('users', ['id' => $id, 'stf_no' => null]);
    }

    /** (g) cross-table joins for reports (c)(f)(h)(i)(k)(n) */
    public function test_report_joins(): void
    {
        DB::table('Wd')->insert(['Wd_No' => 'WDJ1', 'Wd_Name' => 'Ortho']);
        DB::table('Stf')->insert(['Stf_No' => 'SSJ1', 'FirstName' => 'A', 'Alloc_Wd_No' => 'WDJ1']);
        DB::table('StfRota')->insert(['StfRota_No' => 'RJ1', 'Stf_No' => 'SSJ1', 'Wd_No' => 'WDJ1', 'WkBegin' => '2004-01-09', 'Shift' => 'Early']);

        $staffByWard = DB::table('StfRota')->join('Stf', 'StfRota.Stf_No', '=', 'Stf.Stf_No')->where('StfRota.Wd_No', 'WDJ1')->count();
        $this->assertEquals(1, $staffByWard);

        DB::table('LocalDr')->insert(['Clinic_No' => 'EJ1']);
        DB::table('Patient')->insert(['Pt_No' => 'PTJ1', 'Clinic_No' => 'EJ1']);
        DB::table('Room')->insert(['Room_No' => 'RMJ1']);
        DB::table('Appointment')->insert(['Appt_No' => 'AAJ1', 'Pt_No' => 'PTJ1', 'Consult_Stf_No' => 'SSJ1', 'Room_No' => 'RMJ1', 'ApptDate' => '2004-02-01', 'ApptTime' => '09:00:00']);
        $apptJoin = DB::table('Appointment')->join('Patient', 'Appointment.Pt_No', '=', 'Patient.Pt_No')->where('Appointment.Appt_No', 'AAJ1')->count();
        $this->assertEquals(1, $apptJoin);

        DB::table('Supplier')->insert(['Suppl_No' => 'SJ1', 'Name' => 'Sup']);
        DB::table('CentralStock')->insert(['Item_No' => 'ITJ1', 'Suppl_No' => 'SJ1', 'CostPerUnit' => 2.0]);
        DB::table('Wardrequisitions')->insert(['Wd_Req_No' => 'WRJ1', 'Stf_No' => 'SSJ1', 'Wd_No' => 'WDJ1', 'status' => 'Pending']);
        DB::table('Itemrequest')->insert(['Wd_Req_No' => 'WRJ1', 'Item_No' => 'ITJ1', 'QtyReq' => 5]);
        $supplyJoin = DB::table('Wardrequisitions')->join('Itemrequest', 'Wardrequisitions.Wd_Req_No', '=', 'Itemrequest.Wd_Req_No')->join('CentralStock', 'Itemrequest.Item_No', '=', 'CentralStock.Item_No')->where('Wardrequisitions.Wd_No', 'WDJ1')->count();
        $this->assertEquals(1, $supplyJoin);
    }
}
