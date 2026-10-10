<?php

namespace Tests\Feature\Models;

use App\Enums\MaterialType;
use App\Enums\MaterialUnit;
use App\Models\Material;
use App\Models\MaterialPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_type_and_unit_to_enums(): void
    {
        $material = Material::factory()->create([
            'type' => MaterialType::Roving,
            'unit' => MaterialUnit::Kilogram,
        ]);

        $fresh = $material->fresh();

        $this->assertSame(MaterialType::Roving, $fresh->type);
        $this->assertSame(MaterialUnit::Kilogram, $fresh->unit);
    }

    public function test_is_active_by_default(): void
    {
        $material = Material::factory()->create();

        $this->assertTrue($material->fresh()->is_active);
    }

    public function test_inactive_state(): void
    {
        $material = Material::factory()->inactive()->create();

        $this->assertFalse($material->fresh()->is_active);
    }

    public function test_to_material_ref(): void
    {
        $material = Material::factory()->create([
            'name' => 'Стекломат 450',
            'areal_density_gsm' => 450.0,
        ]);

        $ref = $material->toMaterialRef();

        $this->assertSame($material->id, $ref->id);
        $this->assertSame('Стекломат 450', $ref->name);
        $this->assertSame(450.0, $ref->arealDensityGsm);
    }

    public function test_to_material_ref_defaults_null_density_to_zero(): void
    {
        $material = Material::factory()->resin()->create();

        $ref = $material->toMaterialRef();

        $this->assertSame(0.0, $ref->arealDensityGsm);
    }

    public function test_has_many_prices(): void
    {
        $material = Material::factory()->create();
        MaterialPrice::factory()->count(3)->create(['material_id' => $material->id]);

        $this->assertCount(3, $material->prices);
    }

    public function test_current_price_at_picks_latest_price_not_after_given_date(): void
    {
        $material = Material::factory()->create();

        $old = MaterialPrice::factory()->create([
            'material_id' => $material->id,
            'price_minor' => 10000,
            'valid_from' => '2026-01-01 00:00:00',
        ]);
        $current = MaterialPrice::factory()->create([
            'material_id' => $material->id,
            'price_minor' => 20000,
            'valid_from' => '2026-06-01 00:00:00',
        ]);
        MaterialPrice::factory()->create([
            'material_id' => $material->id,
            'price_minor' => 30000,
            'valid_from' => '2026-09-01 00:00:00', // позже даты расчёта — не должен учитываться
        ]);

        $result = $material->currentPriceAt(new \DateTimeImmutable('2026-07-01 00:00:00'));

        $this->assertNotNull($result);
        $this->assertTrue($result->is($current));
        $this->assertSame(20000, $result->price_minor);
    }

    public function test_current_price_at_returns_null_when_no_price_valid_yet(): void
    {
        $material = Material::factory()->create();
        MaterialPrice::factory()->create([
            'material_id' => $material->id,
            'valid_from' => '2026-06-01 00:00:00',
        ]);

        $result = $material->currentPriceAt(new \DateTimeImmutable('2026-01-01 00:00:00'));

        $this->assertNull($result);
    }
}