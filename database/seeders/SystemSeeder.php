<?php

namespace Database\Seeders;

use App\Models\System;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systems = [
            ['name' => 'Gaśnice', 'prefix' => 'GA', 'has_periodic_review' => true],
            ['name' => 'Hydranty', 'prefix' => 'HY', 'has_periodic_review' => true],
            ['name' => 'System Sygnalizacji Pożaru SSP', 'prefix' => 'SSP', 'has_periodic_review' => true],
            ['name' => 'Dźwiękowy System Ostrzegawczy DSO', 'prefix' => 'DSO', 'has_periodic_review' => true],
            ['name' => 'System Oddymiania', 'prefix' => 'ODD', 'has_periodic_review' => true],
            ['name' => 'Bramy i grodzie przeciwpożarowe', 'prefix' => 'BRAM', 'has_periodic_review' => true],
            ['name' => 'Detekcja gazów', 'prefix' => 'DET', 'has_periodic_review' => true],
            ['name' => 'Drzwi przeciwpożarowe', 'prefix' => 'DRZ', 'has_periodic_review' => true],
            ['name' => 'Klapy pożarowe', 'prefix' => 'KLP', 'has_periodic_review' => true],
            ['name' => 'Oświetlenie awaryjne i ewakuacyjne', 'prefix' => 'OSW', 'has_periodic_review' => true],
            ['name' => 'Przeciwpożarowy wyłącznik prądu', 'prefix' => 'PWP', 'has_periodic_review' => true],
            ['name' => 'Wentylacja pożarowa', 'prefix' => 'WEN', 'has_periodic_review' => true],
            ['name' => 'Protokoły serwisowe - z wizyt serwisowych', 'prefix' => 'SER', 'has_periodic_review' => false],
            ['name' => 'Badanie szczelności węży hydrantowych', 'prefix' => 'WEZ', 'has_periodic_review' => false],
        ];

        foreach ($systems as $system) {
            System::updateOrCreate(
                ['slug' => Str::slug($system['name'])],
                [
                    'name' => $system['name'],
                    'prefix' => $system['prefix'],
                    'has_periodic_review' => $system['has_periodic_review'],
                ]
            );
        }
    }
}
