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
            // Test accounts per role (ADR-0012): one staff row per missing position.
            [
                'Stf_No' => 'S1006',
                'FirstName' => 'Rachel',
                'LastName' => 'Kim',
                'Address' => '17 Park Street, Bristol',
                'TelNo' => '0117 555 0106',
                'DOB' => '1979-05-25',
                'Sex' => 'F',
                'NIN' => 'MN112233A',
                'Alloc_Wd_No' => 'WD03',
                'positions' => [
                    ['Pos_No' => 'P008', 'CurrSalary' => 95000, 'HrsPerWk' => 40, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'MBBS', 'QualDate' => '2003-06-20', 'Institution' => 'Medical College London'],
                    ['Type' => 'FRCS', 'QualDate' => '2011-02-14', 'Institution' => 'RCS'],
                ],
                'experiences' => [
                    ['Organization' => 'Bristol Royal', 'Position' => 'Registrar', 'StartDate' => '2012-04-01', 'FinishDate' => '2018-09-30'],
                ],
            ],
            [
                'Stf_No' => 'S1007',
                'FirstName' => 'Daniel',
                'LastName' => 'Hughes',
                'Address' => '29 Gloucester Road, Bristol',
                'TelNo' => '0117 555 0107',
                'DOB' => '1986-12-08',
                'Sex' => 'M',
                'NIN' => 'OP445566B',
                'Alloc_Wd_No' => 'WD02',
                'positions' => [
                    ['Pos_No' => 'P004', 'CurrSalary' => 42000, 'HrsPerWk' => 37.5, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'BSc Nursing', 'QualDate' => '2008-07-12', 'Institution' => 'City University'],
                    ['Type' => 'RGN', 'QualDate' => '2008-11-01', 'Institution' => 'NMC'],
                ],
                'experiences' => [
                    ['Organization' => 'General Infirmary', 'Position' => 'Staff Nurse', 'StartDate' => '2009-02-01', 'FinishDate' => '2015-05-31'],
                ],
            ],
            [
                'Stf_No' => 'S1008',
                'FirstName' => 'Aisha',
                'LastName' => 'Khan',
                'Address' => '6 Victoria Square, Bath',
                'TelNo' => '01225 555 0108',
                'DOB' => '1998-03-17',
                'Sex' => 'F',
                'NIN' => 'QR778899C',
                'Alloc_Wd_No' => 'WD04',
                'positions' => [
                    ['Pos_No' => 'P007', 'CurrSalary' => 22000, 'HrsPerWk' => 37.5, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'NVQ Level 3 Health Care', 'QualDate' => '2019-06-25', 'Institution' => 'Bath College'],
                ],
                'experiences' => [
                    ['Organization' => 'Care Home Bath', 'Position' => 'Care Assistant', 'StartDate' => '2019-09-01', 'FinishDate' => '2022-12-31'],
                ],
            ],
            [
                'Stf_No' => 'S1009',
                'FirstName' => 'Oliver',
                'LastName' => 'Smith',
                'Address' => '11 Richmond Hill, Bristol',
                'TelNo' => '0117 555 0109',
                'DOB' => '1990-09-09',
                'Sex' => 'M',
                'NIN' => 'ST001122D',
                'Alloc_Wd_No' => 'WD11',
                'positions' => [
                    ['Pos_No' => 'P009', 'CurrSalary' => 34000, 'HrsPerWk' => 37.5, 'ContractType' => 'Permanent', 'PaymentType' => 'Monthly'],
                ],
                'qualifications' => [
                    ['Type' => 'BSc Physiotherapy', 'QualDate' => '2012-07-10', 'Institution' => 'Weston University'],
                ],
                'experiences' => [
                    ['Organization' => 'Sports Injury Clinic', 'Position' => 'Physiotherapist', 'StartDate' => '2013-01-15', 'FinishDate' => '2020-06-30'],
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
            ['Stf_No' => 'S1006', 'Wd_No' => 'WD03', 'WkBegin' => today()->startOfWeek()->toDateString(), 'Shift' => 'Morning'],
            ['Stf_No' => 'S1007', 'Wd_No' => 'WD02', 'WkBegin' => today()->startOfWeek()->toDateString(), 'Shift' => 'Evening'],
            ['Stf_No' => 'S1008', 'Wd_No' => 'WD04', 'WkBegin' => today()->startOfWeek()->toDateString(), 'Shift' => 'Morning'],
            ['Stf_No' => 'S1009', 'Wd_No' => 'WD11', 'WkBegin' => today()->startOfWeek()->toDateString(), 'Shift' => 'Morning'],
        ];

        foreach ($rotas as $rota) {
            StfRota::firstOrCreate(
                ['Stf_No' => $rota['Stf_No'], 'Wd_No' => $rota['Wd_No'], 'WkBegin' => $rota['WkBegin'], 'Shift' => $rota['Shift']],
                ['StfRota_No' => 'R'.strtoupper(substr(uniqid(), -9))],
            );
        }
    }
}
