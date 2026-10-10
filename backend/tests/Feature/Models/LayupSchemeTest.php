<?php

namespace Tests\Feature\Models;

use App\Models\LayupLayer;
use App\Models\LayupScheme;
use App\Models\Material;
use App\Models\User;
use Domain\ValueObject\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayupSchemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_is_template_to_bool(): void
    {
        $scheme = LayupScheme::factory()->template()->create();

        $this->assertTrue($scheme->fresh()->is_template);
    }

    public function test_is_template_defaults_to_false(): void
    {
        $scheme = LayupScheme::factory()->create();

        $this->assertFalse($scheme->fresh()->is_template);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $scheme = LayupScheme::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($scheme->user->is($user));
    }

    public function test_created_at_is_set_without_updated_at_column(): void
    {
        $scheme = LayupScheme::factory()->create();

        $this->assertNotNull($scheme->fresh()->created_at);
        $this->assertArrayNotHasKey('updated_at', $scheme->fresh()->getAttributes());
    }

    public function test_layers_are_ordered_by_position(): void
    {
        $scheme = LayupScheme::factory()->create();
        $second = LayupLayer::factory()->create(['scheme_id' => $scheme->id, 'position' => 1]);
        $first = LayupLayer::factory()->create(['scheme_id' => $scheme->id, 'position' => 0]);

        $layers = $scheme->fresh()->layers;

        $this->assertSame($first->id, $layers[0]->id);
        $this->assertSame($second->id, $layers[1]->id);
    }

    public function test_to_domain_builds_working_scheme(): void
    {
        $scheme = LayupScheme::factory()->create();
        $materialA = Material::factory()->create(['areal_density_gsm' => 300.0]);
        $materialB = Material::factory()->create(['areal_density_gsm' => 450.0]);

        LayupLayer::factory()->create([
            'scheme_id' => $scheme->id,
            'material_id' => $materialA->id,
            'position' => 0,
            'coverage' => 1.0,
            'overlap_waste' => 0.0,
        ]);
        LayupLayer::factory()->create([
            'scheme_id' => $scheme->id,
            'material_id' => $materialB->id,
            'position' => 1,
            'coverage' => 1.0,
            'overlap_waste' => 0.0,
        ]);

        $domain = $scheme->fresh()->toDomain();
        $mass = $domain->totalReinforcementMass(Area::fromM2(2.0));

        // 2 м² x 300 г/м² + 2 м² x 450 г/м² = 600 + 900 = 1500 г
        $this->assertEqualsWithDelta(1500.0, $mass->g(), 1e-9);
    }
}