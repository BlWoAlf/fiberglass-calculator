<?php

namespace Tests\Feature\Models;

use App\Enums\MaterialUnit;
use App\Models\Material;
use App\Models\MaterialPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_per_unit_and_price_minor(): void
    {
        $price = MaterialPrice::factory()->create([
            'price_minor' => 45000,
            'per_unit' => MaterialUnit::Kilogram,
        ]);

        $fresh = $price->fresh();

        $this->assertSame(45000, $fresh->price_minor);
        $this->assertSame(MaterialUnit::Kilogram, $fresh->per_unit);
    }

    public function test_belongs_to_material(): void
    {
        $material = Material::factory()->create();
        $price = MaterialPrice::factory()->create(['material_id' => $material->id]);

        $this->assertTrue($price->material->is($material));
    }

    public function test_to_money(): void
    {
        $price = MaterialPrice::factory()->create([
            'price_minor' => 45000,
            'currency' => 'RUB',
        ]);

        $money = $price->toMoney();

        $this->assertSame(45000, $money->minorUnits());
        $this->assertSame('RUB', $money->currency());
    }

    public function test_has_no_timestamps_columns(): void
    {
        $price = MaterialPrice::factory()->create();

        $this->assertArrayNotHasKey('created_at', $price->fresh()->getAttributes());
        $this->assertArrayNotHasKey('updated_at', $price->fresh()->getAttributes());
    }
}