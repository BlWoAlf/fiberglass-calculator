<?php

namespace Database\Factories;

use App\Enums\InputMode;
use App\Models\Calculation;
use App\Models\LayupScheme;
use App\Models\Process;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Calculation>
 */
class CalculationFactory extends Factory
{
    protected $model = Calculation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'input_mode' => InputMode::Quick,
            'scheme_id' => LayupScheme::factory(),
            'process_id' => Process::factory(),
            'quick_params' => ['shape' => 'sphere', 'dimensions' => ['radius_mm' => 500]],
            'effective_area_mm2' => 3_141_592.0,
            'results' => ['mass_total_g' => 1500.0, 'total_cost_minor' => 107690],
            'mass_total_g' => 1500.0,
            'total_cost_minor' => 107690,
            'currency' => 'RUB',
            'prices_snapshot' => [1 => 30000, 2 => 22000],
            'temperature_c' => 20.0,
        ];
    }
}