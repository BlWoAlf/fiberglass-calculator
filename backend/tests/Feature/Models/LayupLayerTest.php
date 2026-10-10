<?php

namespace Tests\Feature\Models;

use App\Models\LayupLayer;
use App\Models\LayupScheme;
use App\Models\Material;
use Domain\ValueObject\Area;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayupLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_numeric_fields(): void
    {
        $layer = LayupLayer::factory()->create([
            'position' => 2,
            'coverage' => 0.8,
            'overlap_waste' => 0.15,
        ]);

        $fresh = $layer->fresh();

        $this->assertSame(2, $fresh->position);
        $this->assertSame(0.8, $fresh->coverage);
        $this->assertSame(0.15, $fresh->overlap_waste);
    }

    public function test_belongs_to_scheme_and_material(): void
    {
        $scheme = LayupScheme::factory()->create();
        $material = Material::factory()->create();
        $layer = LayupLayer::factory()->create([
            'scheme_id' => $scheme->id,
            'material_id' => $material->id,
        ]);

        $this->assertTrue($layer->scheme->is($scheme));
        $this->assertTrue($layer->material->is($material));
    }

    public function test_duplicate_position_within_same_scheme_throws(): void
    {
        $scheme = LayupScheme::factory()->create();
        LayupLayer::factory()->create(['scheme_id' => $scheme->id, 'position' => 0]);

        $this->expectException(QueryException::class);

        LayupLayer::factory()->create(['scheme_id' => $scheme->id, 'position' => 0]);
    }

    public function test_same_position_allowed_in_different_schemes(): void
    {
        $schemeA = LayupScheme::factory()->create();
        $schemeB = LayupScheme::factory()->create();

        LayupLayer::factory()->create(['scheme_id' => $schemeA->id, 'position' => 0]);
        $layerB = LayupLayer::factory()->create(['scheme_id' => $schemeB->id, 'position' => 0]);

        $this->assertNotNull($layerB->fresh());
    }

    public function test_to_domain_builds_working_layer(): void
    {
        $material = Material::factory()->create(['areal_density_gsm' => 300.0]);
        $layer = LayupLayer::factory()->create([
            'material_id' => $material->id,
            'coverage' => 1.0,
            'overlap_waste' => 0.0,
        ]);

        $domain = $layer->toDomain();
        $mass = $domain->massFor(Area::fromM2(2.0));

        // 2 м² x 100% x (1+0) x 300 г/м² = 600 г
        $this->assertEqualsWithDelta(600.0, $mass->g(), 1e-9);
    }
}