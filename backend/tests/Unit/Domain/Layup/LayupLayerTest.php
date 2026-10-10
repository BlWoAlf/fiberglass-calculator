<?php

namespace Tests\Unit\Domain\Layup;

use Domain\Layup\LayupLayer;
use Domain\Layup\MaterialRef;
use Domain\ValueObject\Area;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LayupLayerTest extends TestCase
{
    private function material(float $arealDensityGsm = 450.0): MaterialRef
    {
        return new MaterialRef(id: 1, name: 'Стекломат 450', arealDensityGsm: $arealDensityGsm);
    }

    public function test_creates_with_valid_data(): void
    {
        $layer = new LayupLayer(
            material: $this->material(),
            coverage: 0.8,
            overlapWaste: 0.1,
            position: 0,
        );

        $this->assertSame(0.8, $layer->coverage);
        $this->assertSame(0.1, $layer->overlapWaste);
        $this->assertSame(0, $layer->position);
    }

    public function test_allows_boundary_coverage_values(): void
    {
        $zero = new LayupLayer($this->material(), coverage: 0.0, overlapWaste: 0.0, position: 0);
        $full = new LayupLayer($this->material(), coverage: 1.0, overlapWaste: 1.0, position: 0);

        $this->assertSame(0.0, $zero->coverage);
        $this->assertSame(1.0, $full->coverage);
    }

    public function test_rejects_coverage_above_one(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupLayer($this->material(), coverage: 1.1, overlapWaste: 0.0, position: 0);
    }

    public function test_rejects_negative_coverage(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupLayer($this->material(), coverage: -0.1, overlapWaste: 0.0, position: 0);
    }

    public function test_rejects_overlap_waste_above_one(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupLayer($this->material(), coverage: 0.8, overlapWaste: 1.1, position: 0);
    }

    public function test_rejects_negative_overlap_waste(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupLayer($this->material(), coverage: 0.8, overlapWaste: -0.1, position: 0);
    }

    public function test_rejects_negative_position(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupLayer($this->material(), coverage: 0.8, overlapWaste: 0.1, position: -1);
    }

    public function test_rejects_non_finite_coverage(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LayupLayer($this->material(), coverage: NAN, overlapWaste: 0.1, position: 0);
    }

    public function test_mass_for_without_overlap_waste(): void
    {
        $layer = new LayupLayer(
            material: $this->material(arealDensityGsm: 300.0),
            coverage: 1.0,
            overlapWaste: 0.0,
            position: 0,
        );

        // 2 м² x 100% покрытия x (1+0) x 300 г/м² = 600 г
        $mass = $layer->massFor(Area::fromM2(2.0));

        $this->assertEqualsWithDelta(600.0, $mass->g(), 1e-9);
    }

    public function test_mass_for_applies_coverage_and_overlap_waste(): void
    {
        $layer = new LayupLayer(
            material: $this->material(arealDensityGsm: 300.0),
            coverage: 0.5,
            overlapWaste: 0.1,
            position: 0,
        );

        // 2 м² x 50% покрытия x (1+0.1) x 300 г/м² = 330 г
        $mass = $layer->massFor(Area::fromM2(2.0));

        $this->assertEqualsWithDelta(330.0, $mass->g(), 1e-9);
    }
}