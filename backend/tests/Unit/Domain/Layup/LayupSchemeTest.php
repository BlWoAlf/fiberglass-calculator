<?php

namespace Tests\Unit\Domain\Layup;

use Domain\Layup\LayupLayer;
use Domain\Layup\LayupScheme;
use Domain\Layup\MaterialRef;
use Domain\ValueObject\Area;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LayupSchemeTest extends TestCase
{
    private function layer(int $position, float $arealDensityGsm = 300.0, float $coverage = 1.0, float $overlapWaste = 0.0): LayupLayer
    {
        return new LayupLayer(
            material: new MaterialRef(id: $position + 1, name: "Material {$position}", arealDensityGsm: $arealDensityGsm),
            coverage: $coverage,
            overlapWaste: $overlapWaste,
            position: $position,
        );
    }

    public function test_creates_with_valid_layers(): void
    {
        $scheme = new LayupScheme([$this->layer(0), $this->layer(1)]);

        $this->assertSame(2, $scheme->layerCount());
    }

    public function test_rejects_empty_layers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupScheme([]);
    }

    public function test_rejects_non_layup_layer_entries(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupScheme(['not a layer']);
    }

    public function test_rejects_duplicate_positions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupScheme([$this->layer(0), $this->layer(0)]);
    }

    public function test_layers_are_sorted_by_position(): void
    {
        $second = $this->layer(1);
        $first = $this->layer(0);

        $scheme = new LayupScheme([$second, $first]);

        $this->assertSame([$first, $second], $scheme->layers());
    }

    public function test_total_reinforcement_mass_sums_all_layers(): void
    {
        $scheme = new LayupScheme([
            $this->layer(position: 0, arealDensityGsm: 300.0),
            $this->layer(position: 1, arealDensityGsm: 450.0),
        ]);

        // 2 м² x 300 г/м² + 2 м² x 450 г/м² = 600 + 900 = 1500 г
        $mass = $scheme->totalReinforcementMass(Area::fromM2(2.0));

        $this->assertEqualsWithDelta(1500.0, $mass->g(), 1e-9);
    }

    public function test_total_reinforcement_mass_respects_coverage(): void
    {
        $scheme = new LayupScheme([
            $this->layer(position: 0, arealDensityGsm: 300.0, coverage: 1.0),
            $this->layer(position: 1, arealDensityGsm: 300.0, coverage: 0.5), // локальное усиление
        ]);

        // 2 м² x 100% x 300 г/м² + 2 м² x 50% x 300 г/м² = 600 + 300 = 900 г
        $mass = $scheme->totalReinforcementMass(Area::fromM2(2.0));

        $this->assertEqualsWithDelta(900.0, $mass->g(), 1e-9);
    }
}