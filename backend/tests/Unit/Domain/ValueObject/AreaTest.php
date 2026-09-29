<?php

namespace Tests\Unit\Domain\ValueObject;

use Domain\ValueObject\Area;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AreaTest extends TestCase
{
    public function test_creates_from_mm2(): void
    {
        $area = Area::fromMm2(31415.9);

        $this->assertSame(31415.9, $area->mm2());
    }

    public function test_creates_from_m2(): void
    {
        $area = Area::fromM2(2.5);

        $this->assertSame(2_500_000.0, $area->mm2());
        $this->assertSame(2.5, $area->m2());
    }

    public function test_zero_is_zero(): void
    {
        $this->assertTrue(Area::zero()->isZero());
    }

    public function test_rejects_negative_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Area::fromMm2(-1.0);
    }

    public function test_rejects_non_finite_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Area::fromMm2(NAN);
    }

    public function test_add(): void
    {
        $result = Area::fromMm2(100.0)->add(Area::fromMm2(50.0));

        $this->assertSame(150.0, $result->mm2());
    }

    public function test_subtract(): void
    {
        $result = Area::fromMm2(100.0)->subtract(Area::fromMm2(40.0));

        $this->assertSame(60.0, $result->mm2());
    }

    public function test_subtract_below_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Area::fromMm2(10.0)->subtract(Area::fromMm2(20.0));
    }

    public function test_multiply(): void
    {
        $result = Area::fromMm2(200.0)->multiply(1.15);

        $this->assertEqualsWithDelta(230.0, $result->mm2(), 1e-9);
    }

    public function test_divide(): void
    {
        $result = Area::fromMm2(200.0)->divide(2.0);

        $this->assertSame(100.0, $result->mm2());
    }

    public function test_divide_by_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Area::fromMm2(200.0)->divide(0.0);
    }

    public function test_equals_within_epsilon(): void
    {
        $this->assertTrue(Area::fromMm2(100.0)->equals(Area::fromMm2(100.0 + 1e-10)));
        $this->assertFalse(Area::fromMm2(100.0)->equals(Area::fromMm2(100.1)));
    }

    public function test_greater_than_and_less_than(): void
    {
        $bigger = Area::fromMm2(200.0);
        $smaller = Area::fromMm2(100.0);

        $this->assertTrue($bigger->greaterThan($smaller));
        $this->assertTrue($smaller->lessThan($bigger));
        $this->assertFalse($smaller->greaterThan($bigger));
    }

    public function test_is_immutable(): void
    {
        $original = Area::fromMm2(100.0);
        $original->add(Area::fromMm2(50.0));

        $this->assertSame(100.0, $original->mm2());
    }

    public function test_to_string(): void
    {
        $this->assertSame('100.0000 mm²', (string) Area::fromMm2(100.0));
    }

    public function test_to_mass(): void
    {
        // 2 m² покрытия материалом 300 г/м² -> 600 г
        $mass = Area::fromM2(2.0)->toMass(300.0);

        $this->assertEqualsWithDelta(600.0, $mass->g(), 1e-9);
    }

    public function test_to_mass_rejects_negative_areal_density(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Area::fromM2(1.0)->toMass(-10.0);
    }
}
