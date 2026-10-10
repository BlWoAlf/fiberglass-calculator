<?php

namespace Tests\Unit\Domain\Layup;

use Domain\Layup\MaterialRef;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MaterialRefTest extends TestCase
{
    public function test_creates_with_valid_data(): void
    {
        $material = new MaterialRef(id: 5, name: 'Стекломат 450', arealDensityGsm: 450.0);

        $this->assertSame(5, $material->id);
        $this->assertSame('Стекломат 450', $material->name);
        $this->assertSame(450.0, $material->arealDensityGsm);
    }

    public function test_allows_zero_areal_density(): void
    {
        $material = new MaterialRef(id: 1, name: 'Material', arealDensityGsm: 0.0);

        $this->assertSame(0.0, $material->arealDensityGsm);
    }

    public function test_rejects_non_positive_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MaterialRef(id: 0, name: 'Material', arealDensityGsm: 100.0);
    }

    public function test_rejects_negative_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MaterialRef(id: -1, name: 'Material', arealDensityGsm: 100.0);
    }

    public function test_rejects_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MaterialRef(id: 1, name: '', arealDensityGsm: 100.0);
    }

    public function test_rejects_blank_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MaterialRef(id: 1, name: '   ', arealDensityGsm: 100.0);
    }

    public function test_rejects_negative_areal_density(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MaterialRef(id: 1, name: 'Material', arealDensityGsm: -10.0);
    }

    public function test_rejects_non_finite_areal_density(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MaterialRef(id: 1, name: 'Material', arealDensityGsm: NAN);
    }
}