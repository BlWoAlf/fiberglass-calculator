<?php

namespace Database\Factories;

use App\Models\LayupLayer;
use App\Models\LayupScheme;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LayupLayer>
 */
class LayupLayerFactory extends Factory
{
    protected $model = LayupLayer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scheme_id' => LayupScheme::factory(),
            'position' => 0,
            'material_id' => Material::factory(),
            'coverage' => 1.0,
            'overlap_waste' => 0.1,
            'note' => null,
        ];
    }
}