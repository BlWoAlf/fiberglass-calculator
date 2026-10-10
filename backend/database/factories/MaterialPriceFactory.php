<?php

namespace Database\Factories;

use App\Enums\MaterialUnit;
use App\Models\Material;
use App\Models\MaterialPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialPrice>
 */
class MaterialPriceFactory extends Factory
{
    protected $model = MaterialPrice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'material_id' => Material::factory(),
            'price_minor' => fake()->numberBetween(10_000, 100_000),
            'currency' => 'RUB',
            'per_unit' => MaterialUnit::Kilogram,
            'vendor_sku' => fake()->bothify('SKU-####'),
            'valid_from' => now(),
        ];
    }
}