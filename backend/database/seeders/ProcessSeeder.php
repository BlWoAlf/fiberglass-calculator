<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Seeder;

class ProcessSeeder extends Seeder
{
    /**
     * Три технологии из раздела 3.2 design-документа.
     *
     * resin_ratio/waste_factor/hardener_curve — стартовые ориентиры,
     * не калибровка под конкретную мастерскую (см. notes каждой записи).
     */
    public function run(): void
    {
        $hardenerCurve = [
            ['temp_c' => 10, 'ratio' => 0.04],
            ['temp_c' => 20, 'ratio' => 0.02],
            ['temp_c' => 30, 'ratio' => 0.015],
        ];

        $notes = 'Значения по умолчанию — ориентир, требуют калибровки под конкретную практику мастерской.';

        Process::create([
            'code' => 'manual_layup',
            'name' => 'Ручная укладка',
            'resin_ratio' => 2.2,
            'waste_factor' => 0.20,
            'hardener_curve' => $hardenerCurve,
            'notes' => $notes,
        ]);

        Process::create([
            'code' => 'vacuum_bag',
            'name' => 'Вакуумный мешок',
            'resin_ratio' => 1.5,
            'waste_factor' => 0.12,
            'hardener_curve' => $hardenerCurve,
            'notes' => $notes,
        ]);

        Process::create([
            'code' => 'infusion',
            'name' => 'Инфузия',
            'resin_ratio' => 1.2,
            'waste_factor' => 0.05,
            'hardener_curve' => $hardenerCurve,
            'notes' => $notes,
        ]);
    }
}