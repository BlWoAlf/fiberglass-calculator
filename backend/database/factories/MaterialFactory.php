<?php

namespace Database\Factories;

use App\Enums\MaterialType;
use App\Enums\MaterialUnit;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => MaterialType::Mat,
            'name' => fake()->words(2, true),
            'areal_density_gsm' => fake()->randomFloat(2, 200, 600),
            'bulk_density' => null,
            'unit' => MaterialUnit::Kilogram,
            'vendor' => fake()->company(),
            'is_active' => true,
        ];
    }

    public function resin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MaterialType::Resin,
            'areal_density_gsm' => null,
            'bulk_density' => fake()->randomFloat(2, 1100, 1250),
            'unit' => MaterialUnit::Kilogram,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}