<?php

namespace Tests\Feature\Models;

use App\Models\Process;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_numeric_and_json_fields(): void
    {
        $process = Process::factory()->create([
            'resin_ratio' => 1.5,
            'waste_factor' => 0.1,
            'hardener_curve' => [['temp_c' => 20.0, 'ratio' => 0.3]],
        ]);

        $fresh = $process->fresh();

        $this->assertSame(1.5, $fresh->resin_ratio);
        $this->assertSame(0.1, $fresh->waste_factor);
        $this->assertIsArray($fresh->hardener_curve);
        // JSON не различает int/float для целых значений (20.0 -> "20" -> (int) 20 при декодировании),
        // поэтому сравниваем численно, а не точным типом
        $this->assertEqualsWithDelta(20.0, $fresh->hardener_curve[0]['temp_c'], 1e-9);
        $this->assertEqualsWithDelta(0.3, $fresh->hardener_curve[0]['ratio'], 1e-9);
    }

    public function test_code_must_be_unique(): void
    {
        Process::factory()->create(['code' => 'vacuum_bag']);

        $this->expectException(QueryException::class);

        Process::factory()->create(['code' => 'vacuum_bag']);
    }

    public function test_to_domain_builds_working_process(): void
    {
        $process = Process::factory()->create([
            'resin_ratio' => 1.5,
            'waste_factor' => 0.1,
            'hardener_curve' => [
                ['temp_c' => 15.0, 'ratio' => 0.40],
                ['temp_c' => 25.0, 'ratio' => 0.30],
            ],
        ]);

        $domain = $process->toDomain();

        $this->assertSame(1.5, $domain->resinRatio);
        $this->assertSame(0.1, $domain->wasteFactor);
        // середина отрезка 15..25 -> середина отрезка 0.40..0.30
        $this->assertEqualsWithDelta(0.35, $domain->hardenerRatio(20.0), 1e-9);
    }
}