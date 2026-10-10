<?php

namespace Database\Factories;

use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
{
    protected $model = Process::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'resin_ratio' => fake()->randomFloat(3, 1.1, 2.5),
            'waste_factor' => fake()->randomFloat(3, 0.05, 0.25),
            'hardener_curve' => [
                ['temp_c' => 15, 'ratio' => 0.4],
                ['temp_c' => 25, 'ratio' => 0.3],
            ],
            'notes' => null,
        ];
    }
}
