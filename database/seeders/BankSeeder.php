<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            ['id' => '626EFE22-06F5-4146-940B-12C4F6E17118', 'name' => 'AGROINDUSTRISKO KOMERCIJALNA BANKA AIK BANKA, AD, BEOGRAD', 'code' => '105', 'swift' => 'AIKBRS22', 'hide' => false, 'agencies_count' => 117],
            ['id' => '6529FC27-2CC6-456C-9482-74BCCEE14398', 'name' => 'YETTEL BANK AD BEOGRAD', 'code' => '115', 'swift' => 'AAAARSBG', 'hide' => false, 'agencies_count' => 38],
            ['id' => 'CF32A2FC-049A-493C-9A42-EE2489FA8F62', 'name' => 'ADRIATIC BANK AKCIONARSKO DRUŠTVO BEOGRAD', 'code' => '145', 'swift' => 'LIKIRSBG', 'hide' => false, 'agencies_count' => 7],
            ['id' => '9CF36B7B-BA04-4548-846C-D2D834E003CE', 'name' => 'EUROBANK DIREKTNA AKCIONARSKO DRUŠTVO BEOGRAD', 'code' => '150', 'swift' => 'ERBKRSBG', 'hide' => false, 'agencies_count' => 46],
            ['id' => '81A00A5A-31FF-49F5-A81D-3EFAC84FF81F', 'name' => 'HALKBANK, AD, BEOGRAD', 'code' => '155', 'swift' => 'CABARS22', 'hide' => false, 'agencies_count' => 51],
            ['id' => 'E1F873C3-5DA7-4A67-9B43-B4D6C408E226', 'name' => 'BANCA INTESA, AD, BEOGRAD', 'code' => '160', 'swift' => 'DBDBRSBG', 'hide' => false, 'agencies_count' => 1513],
            ['id' => 'EC369CFA-B48B-4213-9238-CC83C5242055', 'name' => 'ADDIKO BANK, AD, BEOGRAD', 'code' => '165', 'swift' => 'HAABRSBG', 'hide' => false, 'agencies_count' => 44],
            ['id' => '2764E7CF-F974-487A-99BC-98D01EF4E5FB', 'name' => 'UNICREDIT BANK SRBIJA, AD, BEOGRAD', 'code' => '170', 'swift' => 'BACXRSBG', 'hide' => false, 'agencies_count' => 358],
            ['id' => 'A95C7A6D-914B-4C87-81E6-4512348917B4', 'name' => 'ALTA BANKA  A.D. BEOGRAD', 'code' => '190', 'swift' => 'JMBNRSBG', 'hide' => false, 'agencies_count' => 562],
            ['id' => '53BA4B76-028F-4EE4-91BF-1E4FFDB4593F', 'name' => 'BANKA POŠTANSKA ŠTEDIONICA, AD, BEOGRAD', 'code' => '200', 'swift' => 'SBPORSBG', 'hide' => false, 'agencies_count' => 444],
            ['id' => '6F6899B5-FB1F-4F31-9886-810C65647C52', 'name' => 'NLB KOMERCIJALNA BANKA A.D. BEOGRAD', 'code' => '205', 'swift' => 'KOBBRSBG', 'hide' => false, 'agencies_count' => 568],
            ['id' => 'EB117D2F-188A-4B1B-8AE4-5810E6A372FB', 'name' => 'PROCREDIT BANK, AD, BEOGRAD', 'code' => '220', 'swift' => 'PRCBRSBG', 'hide' => false, 'agencies_count' => 58],
            ['id' => '5E972CC0-4292-4900-99B3-8F06CFD3FA75', 'name' => 'RAIFFEISEN BANKA, AD, BEOGRAD', 'code' => '265', 'swift' => 'RZBSRSBG', 'hide' => false, 'agencies_count' => 3430],
            ['id' => '2ED650E1-DFC4-4892-908B-7FE4BF21815E', 'name' => 'SBERBANK SRBIJA, AD, BEOGRAD', 'code' => '285', 'swift' => 'SABRRSBG', 'hide' => false, 'agencies_count' => 64],
            ['id' => '54A2099B-B065-4756-90A8-6959E4BE68DA', 'name' => 'SRPSKA BANKA, AD, BEOGRAD', 'code' => '295', 'swift' => 'SRBNRSBG', 'hide' => false, 'agencies_count' => 0],
            ['id' => 'B7940851-422D-4998-AF3B-7E4C8FFEA6D6', 'name' => 'OTP BANKA SRBIJA, AD, NOVI SAD', 'code' => '325', 'swift' => 'OTPVRS22', 'hide' => false, 'agencies_count' => 784],
            ['id' => '1FD27014-D941-412F-9130-EFE09C6A7D18', 'name' => 'ERSTE BANK, AD, NOVI SAD', 'code' => '340', 'swift' => 'GIBARS22', 'hide' => false, 'agencies_count' => 481],
            ['id' => '3CCA7012-4F22-4A08-AC3D-80DCC683FD03', 'name' => 'BANKA POŠTANSKA ŠTEDIONICA  AKCIONARSKO DRUŠTVO BEOGRAD – privremeni račun (MTS banka)', 'code' => '360', 'swift' => 'KMEBRS2Z', 'hide' => false, 'agencies_count' => 14],
            ['id' => '818948EA-0A45-4F43-95E6-A4EDB68C02D2', 'name' => '3 BANKA A.D. NOVI SAD', 'code' => '370', 'swift' => 'OPPBRS22', 'hide' => false, 'agencies_count' => 0],
            ['id' => '8C02E59F-C1E4-46A8-B9AD-92AB2B4E5A6A', 'name' => 'API BANK A.D. BEOGRAD', 'code' => '375', 'swift' => 'APIBRSBG', 'hide' => false, 'agencies_count' => 28],
            ['id' => '28806651-7C60-4BF4-9A5D-DB057165D58C', 'name' => 'MIRABANK, AD, BEOGRAD', 'code' => '380', 'swift' => 'MRBNRSBG', 'hide' => false, 'agencies_count' => 0],
            ['id' => '3CA979B3-A1A1-440F-AA46-0D5432702D51', 'name' => 'BANK OF CHINA SRBIJA, AD, BEOGRAD – NOVI BEOGRAD', 'code' => '385', 'swift' => 'BKCHRSBG', 'hide' => false, 'agencies_count' => 1],
            ['id' => 'c0520dbf-0309-43ca-99d6-a4876cb6afdb', 'name' => 'Other', 'code' => '000', 'swift' => null, 'hide' => false, 'agencies_count' => 70],
        ];

        foreach ($banks as $bankData) {
            Bank::updateOrCreate(
                ['code' => $bankData['code']],
                [
                    'external_id' => $bankData['id'],
                    'name' => $bankData['name'],
                    'swift' => $bankData['swift'],
                    'hide' => $bankData['hide'],
                    'agencies_count' => $bankData['agencies_count'],
                ]
            );
        }
    }
}
