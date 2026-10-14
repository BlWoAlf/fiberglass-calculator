<?php

namespace Tests\Feature;

use App\Enums\MaterialType;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Process;
use Database\Seeders\MaterialSeeder;
use Database\Seeders\ProcessSeeder;
use Domain\Layup\LayupCalculator;
use Domain\Layup\LayupLayer;
use Domain\Layup\LayupScheme;
use Domain\Pricing\PriceSnapshot;
use Domain\ValueObject\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_seeder_creates_materials_with_prices(): void
    {
        $this->seed(MaterialSeeder::class);

        $this->assertSame(10, Material::count());
        $this->assertSame(10, MaterialPrice::count());

        $mat = Material::where('name', 'Стекломат 450')->firstOrFail();
        $this->assertSame(450.0, $mat->areal_density_gsm);
        $this->assertNotNull($mat->currentPriceAt(now()));
    }

    public function test_process_seeder_creates_three_processes(): void
    {
        $this->seed(ProcessSeeder::class);

        $this->assertSame(3, Process::count());
        $this->assertNotNull(Process::where('code', 'vacuum_bag')->first());
    }

    public function test_quick_calculation_works_end_to_end_from_seeded_data(): void
    {
        $this->seed([MaterialSeeder::class, ProcessSeeder::class]);

        $reinforcement = Material::where('name', 'Стекломат 450')->firstOrFail();
        $resin = Material::where('type', MaterialType::Resin)->firstOrFail();
        $hardener = Material::where('type', MaterialType::Hardener)->firstOrFail();
        $process = Process::where('code', 'vacuum_bag')->firstOrFail();

        $prices = new PriceSnapshot([
            $reinforcement->id => $reinforcement->currentPriceAt(now())->toMoney(),
            $resin->id => $resin->currentPriceAt(now())->toMoney(),
            $hardener->id => $hardener->currentPriceAt(now())->toMoney(),
        ]);

        $scheme = new LayupScheme([
            new LayupLayer($reinforcement->toMaterialRef(), coverage: 1.0, overlapWaste: 0.0, position: 0),
        ]);

        $breakdown = (new LayupCalculator())->calculate(
            effectiveArea: Area::fromM2(2.0),
            scheme: $scheme,
            process: $process->toDomain(),
            resin: $resin->toMaterialRef(),
            hardener: $hardener->toMaterialRef(),
            prices: $prices,
        );

        $this->assertGreaterThan(0, $breakdown->massTotal->g());
        $this->assertGreaterThan(0, $breakdown->totalCost->minorUnits());
    }
}