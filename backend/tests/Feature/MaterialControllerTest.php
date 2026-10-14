<?php

namespace Tests\Feature;

use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_active_materials(): void
    {
        Material::factory()->create(['name' => 'Активный материал', 'is_active' => true]);
        Material::factory()->inactive()->create(['name' => 'Снятый с продажи']);

        $response = $this->getJson('/api/materials');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['name' => 'Активный материал']);
        $response->assertJsonMissing(['name' => 'Снятый с продажи']);
    }

    public function test_returns_expected_shape(): void
    {
        Material::factory()->create([
            'name' => 'Стекломат 450',
            'areal_density_gsm' => 450.0,
        ]);

        $response = $this->getJson('/api/materials');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'name', 'areal_density_gsm', 'bulk_density', 'unit', 'vendor', 'is_active'],
            ],
        ]);
    }

    public function test_returns_empty_list_when_no_materials_exist(): void
    {
        $response = $this->getJson('/api/materials');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }
}