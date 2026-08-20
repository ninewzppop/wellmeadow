<?php

namespace Database\Seeders;

use App\Models\Stf;
use App\Models\StfPos;
use App\Models\StfQual;
use App\Models\StfRota;
use App\Models\StfWorkExp;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            [
                'Stf_No' => 'S1001',
                'FirstName' => 'Sarah',
                'LastName' => 'Connor',
                'Address' => '12 Oak Street, Bristol',
                'TelNo' => '0117 555 0101',
                'DOB' => '1988-04-12',
                'Sex' => 'F',
                'NIN' => 'AB123456C',
                'Alloc_Wd_No' => 'WD01',
                'positions' => [
                    ['Pos_No' => 'P001', 'CurrSalary' => 32000, 'HrsPerWk' => 37.5, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'BSc Nursing', 'QualDate' => '2010-07-15', 'Institution' => 'City University'],
                    ['Type' => 'RGN', 'QualDate' => '2010-11-01', 'Institution' => 'NMC'],
                ],
                'experiences' => [
                    ['Organization' => 'St Mary\'s Hospital', 'Position' => 'Staff Nurse', 'StartDate' => '2011-02-01', 'FinishDate' => '2016-06-30'],
                ],
            ],
            [
                'Stf_No' => 'S1002',
                'FirstName' => 'James',
                'LastName' => 'Wong',
                'Address' => '45 Elm Avenue, Bristol',
                'TelNo' => '0117 555 0102',
                'DOB' => '1975-09-03',
                'Sex' => 'M',
                'NIN' => 'CD789012E',
                'Alloc_Wd_No' => 'WD03',
                'positions' => [
                    ['Pos_No' => 'P003', 'CurrSalary' => 85000, 'HrsPerWk' => 40, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'MBBS', 'QualDate' => '2000-06-20', 'Institution' => 'Medical College London'],
                    ['Type' => 'MRCP', 'QualDate' => '2006-01-10', 'Institution' => 'RCP'],
                ],
                'experiences' => [
                    ['Organization' => 'Royal Free Hospital', 'Position' => 'Registrar', 'StartDate' => '2007-03-01', 'FinishDate' => '2012-08-31'],
                ],
            ],
            [
                'Stf_No' => 'S1003',
                'FirstName' => 'Priya',
                'LastName' => 'Patel',
                'Address' => '8 Cedar Road, Bristol',
                'TelNo' => '0117 555 0103',
                'DOB' => '1992-11-22',
                'Sex' => 'F',
                'NIN' => 'EF345678G',
                'Alloc_Wd_No' => 'WD01',
                'positions' => [
                    ['Pos_No' => 'P002', 'CurrSalary' => 38000, 'HrsPerWk' => 37.5, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'BSc Nursing', 'QualDate' => '2014-07-01', 'Institution' => 'City University'],
                ],
                'experiences' => [
                    ['Organization' => 'General Infirmary', 'Position' => 'Staff Nurse', 'StartDate' => '2015-01-15', 'FinishDate' => '2019-10-31'],
                    ['Organization' => 'Bristol Royal', 'Position' => 'Senior Staff Nurse', 'StartDate' => '2019-11-01', 'FinishDate' => null],
                ],
            ],
            [
                'Stf_No' => 'S1004',
                'FirstName' => 'Tom',
                'LastName' => 'Bennett',
                'Address' => '23 Pine Grove, Bath',
                'TelNo' => '01225 555 0144',
                'DOB' => '1984-01-30',
                'Sex' => 'M',
                'NIN' => 'GH901234J',
                'Alloc_Wd_No' => 'WD04',
                'positions' => [
                    ['Pos_No' => 'P006', 'CurrSalary' => 28000, 'HrsPerWk' => 35, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'BSc IT', 'QualDate' => '2006-06-15', 'Institution' => 'Weston University'],
                ],
                'experiences' => [
                    ['Organization' => 'NHS Trust IT Dept', 'Position' => 'IT Support Analyst', 'StartDate' => '2007-04-01', 'FinishDate' => '2013-03-31'],
                ],
            ],
            [
                'Stf_No' => 'S1005',
                'FirstName' => 'Emily',
                'LastName' => 'Okafor',
                'Address' => '90 Willow Way, Bristol',
                'TelNo' => '0117 555 0105',
                'DOB' => '1995-07-18',
                'Sex' => 'F',
                'NIN' => 'JK567890L',
                'Alloc_Wd_No' => 'WD02',
                'positions' => [
                    ['Pos_No' => 'P005', 'CurrSalary' => 24000, 'HrsPerWk' => 37.5, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'BA Business Admin', 'QualDate' => '2016-07-10', 'Institution' => 'City University'],
                ],
                'experiences' => [
                    ['Organization' => 'County Council', 'Position' => 'Office Assistant', 'StartDate' => '2017-02-01', 'FinishDate' => '2019-08-30'],
                ],
            ],
        ];

        foreach ($staff as $record) {
            $member = Stf::firstOrCreate(['Stf_No' => $record['Stf_No']], collect($record)->except(['positions', 'qualifications', 'experiences'])->toArray());

            StfPos::where('Stf_No', $member->Stf_No)->delete();
            StfQual::where('Stf_No', $member->Stf_No)->delete();
            StfWorkExp::where('Stf_No', $member->Stf_No)->delete();

            foreach ($record['positions'] as $i => $pos) {
                StfPos::create(['StfPos_No' => "SP{$member->Stf_No}{$i}", 'Stf_No' => $member->Stf_No, ...$pos]);
            }

            foreach ($record['qualifications'] as $i => $qual) {
                StfQual::create(['Qual_No' => "Q{$member->Stf_No}{$i}", 'Stf_No' => $member->Stf_No, ...$qual]);
            }

            foreach ($record['experiences'] as $i => $exp) {
                StfWorkExp::create(['WorkExp_No' => "E{$member->Stf_No}{$i}", 'Stf_No' => $member->Stf_No, ...$exp]);
            }
        }

        $rotas = [
            ['Stf_No' => 'S1001', 'Wd_No' => 'WD01', 'WkBegin' => today()->startOfWeek()->toDateString(), 'Shift' => 'Morning'],
            ['Stf_No' => 'S1003', 'Wd_No' => 'WD01', 'WkBegin' => today()->startOfWeek()->toDateString(), 'Shift' => 'Evening'],
            ['Stf_No' => 'S1002', 'Wd_No' => 'WD03', 'WkBegin' => today()->startOfWeek()->toDateString(), 'Shift' => 'Night'],
            ['Stf_No' => 'S1001', 'Wd_No' => 'WD02', 'WkBegin' => today()->startOfWeek()->addWeek()->toDateString(), 'Shift' => 'Night'],
            ['Stf_No' => 'S1005', 'Wd_No' => 'WD02', 'WkBegin' => today()->startOfWeek()->toDateString(), 'Shift' => 'Morning'],
        ];

        foreach ($rotas as $rota) {
            StfRota::firstOrCreate(
                ['Stf_No' => $rota['Stf_No'], 'Wd_No' => $rota['Wd_No'], 'WkBegin' => $rota['WkBegin'], 'Shift' => $rota['Shift']],
                ['StfRota_No' => 'R'.strtoupper(substr(uniqid(), -9))],
            );
        }
    }
}
