<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (CompanySetting::count() === 0) {
            CompanySetting::create([
                'name' => 'CRM System',
                'address' => 'Ulica Przykładowa 1',
                'postal_code' => '00-000',
                'city' => 'Warszawa',
                'phone' => '+48 123 456 789',
                'email' => 'kontakt@example.com',
                'website' => 'https://example.com',
            ]);
        }
    }
}
