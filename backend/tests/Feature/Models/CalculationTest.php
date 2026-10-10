<?php

namespace Tests\Feature\Models;

use App\Enums\InputMode;
use App\Models\Calculation;
use App\Models\LayupScheme;
use App\Models\Process;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_fields(): void
    {
        $calculation = Calculation::factory()->create([
            'input_mode' => InputMode::Quick,
            'effective_area_mm2' => 123.456,
            'mass_total_g' => 1500.0,
            'total_cost_minor' => 107690,
            'temperature_c' => 20.5,
        ]);

        $fresh = $calculation->fresh();

        $this->assertSame(InputMode::Quick, $fresh->input_mode);
        $this->assertSame(123.456, $fresh->effective_area_mm2);
        $this->assertSame(1500.0, $fresh->mass_total_g);
        $this->assertSame(107690, $fresh->total_cost_minor);
        $this->assertSame(20.5, $fresh->temperature_c);
        $this->assertIsArray($fresh->quick_params);
        $this->assertIsArray($fresh->results);
        $this->assertIsArray($fresh->prices_snapshot);
    }

    public function test_temperature_c_is_nullable(): void
    {
        $calculation = Calculation::factory()->create(['temperature_c' => null]);

        $this->assertNull($calculation->fresh()->temperature_c);
    }

    public function test_belongs_to_user_scheme_and_process(): void
    {
        $user = User::factory()->create();
        $scheme = LayupScheme::factory()->create();
        $process = Process::factory()->create();

        $calculation = Calculation::factory()->create([
            'user_id' => $user->id,
            'scheme_id' => $scheme->id,
            'process_id' => $process->id,
        ]);

        $this->assertTrue($calculation->user->is($user));
        $this->assertTrue($calculation->scheme->is($scheme));
        $this->assertTrue($calculation->process->is($process));
    }

    public function test_created_at_is_set_without_updated_at_column(): void
    {
        $calculation = Calculation::factory()->create();

        $this->assertNotNull($calculation->fresh()->created_at);
        $this->assertArrayNotHasKey('updated_at', $calculation->fresh()->getAttributes());
    }
}