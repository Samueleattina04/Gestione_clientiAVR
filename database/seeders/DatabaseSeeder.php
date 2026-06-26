<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{User, LicenseType};
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        User::firstOrCreate(['email' => 'admin@avrinformatica.it'], [
            'name'     => 'Amministratore',
            'password' => Hash::make('avr2024!'),
        ]);
        $licenses = [
            ['name'=>'Microsoft 365 Business Basic',    'category'=>'Microsoft 365',          'description'=>'App web + Teams + Exchange 50 GB',          'price_monthly'=>5.60,  'price_yearly'=>56.40],
            ['name'=>'Microsoft 365 Business Standard', 'category'=>'Microsoft 365',          'description'=>'App desktop + Teams + Exchange',             'price_monthly'=>11.70, 'price_yearly'=>117.60],
            ['name'=>'Microsoft 365 Business Premium',  'category'=>'Microsoft 365',          'description'=>'Business Standard + sicurezza avanzata',     'price_monthly'=>20.60, 'price_yearly'=>207.60],
            ['name'=>'Microsoft 365 Apps for Business', 'category'=>'Microsoft 365',          'description'=>'App Office desktop + 1 TB OneDrive',        'price_monthly'=>8.25,  'price_yearly'=>82.80],
            ['name'=>'Microsoft 365 E3',                'category'=>'Microsoft 365 Enterprise','description'=>'Enterprise con compliance avanzata',         'price_monthly'=>32.00, 'price_yearly'=>384.00],
            ['name'=>'Microsoft 365 E5',                'category'=>'Microsoft 365 Enterprise','description'=>'Enterprise + sicurezza + analytics',         'price_monthly'=>54.80, 'price_yearly'=>657.60],
            ['name'=>'Office 365 E1',                   'category'=>'Office 365',             'description'=>'App web + Teams + Exchange 50 GB',           'price_monthly'=>7.20,  'price_yearly'=>86.40],
            ['name'=>'Office 365 E3',                   'category'=>'Office 365',             'description'=>'App desktop + Teams + Exchange 100 GB',      'price_monthly'=>18.00, 'price_yearly'=>216.00],
            ['name'=>'Exchange Online Plan 1',           'category'=>'Exchange',               'description'=>'Posta elettronica 50 GB',                    'price_monthly'=>3.40,  'price_yearly'=>40.80],
            ['name'=>'Exchange Online Plan 2',           'category'=>'Exchange',               'description'=>'Posta elettronica 100 GB + archiviazione',   'price_monthly'=>7.00,  'price_yearly'=>84.00],
            ['name'=>'Teams Essentials',                 'category'=>'Teams',                  'description'=>'Solo Microsoft Teams',                       'price_monthly'=>3.30,  'price_yearly'=>39.60],
            ['name'=>'Azure AD Premium P1',             'category'=>'Azure',                  'description'=>'Accesso condizionale + MFA avanzato',        'price_monthly'=>5.60,  'price_yearly'=>67.20],
        ];
        foreach ($licenses as $l) {
            LicenseType::firstOrCreate(['name' => $l['name']], $l);
        }
    }
}
