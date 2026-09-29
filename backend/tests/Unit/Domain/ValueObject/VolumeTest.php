<?php

namespace Tests\Unit\Domain\ValueObject;

use Domain\ValueObject\Area;
use Domain\ValueObject\Volume;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class VolumeTest extends TestCase
{
    public function test_creates_from_mm3(): void
    {
        $volume = Volume::fromMm3(523598.8);

        $this->assertSame(523598.8, $volume->mm3());
    }

    public function test_creates_from_cm3(): void
    {
        $volume = Volume::fromCm3(523.5988);

        $this->assertEqualsWithDelta(523598.8, $volume->mm3(), 1e-6);
        $this->assertEqualsWithDelta(523.5988, $volume->cm3(), 1e-9);
    }

    public function test_creates_from_m3(): void
    {
        $volume = Volume::fromM3(0.5);

        $this->assertSame(500_000_000.0, $volume->mm3());
        $this->assertSame(0.5, $volume->m3());
    }

    public function test_creates_from_liters(): void
    {
        $volume = Volume::fromLiters(2.0);

        $this->assertSame(2_000_000.0, $volume->mm3());
        $this->assertSame(2.0, $volume->liters());
    }

    public function test_zero_is_zero(): void
    {
        $this->assertTrue(Volume::zero()->isZero());
    }

    public function test_rejects_negative_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Volume::fromMm3(-1.0);
    }

    public function test_rejects_non_finite_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Volume::fromMm3(NAN);
    }

    public function test_add(): void
    {
        $result = Volume::fromMm3(100.0)->add(Volume::fromMm3(50.0));

        $this->assertSame(150.0, $result->mm3());
    }

    public function test_subtract(): void
    {
        $result = Volume::fromMm3(100.0)->subtract(Volume::fromMm3(40.0));

        $this->assertSame(60.0, $result->mm3());
    }

    public function test_subtract_below_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Volume::fromMm3(10.0)->subtract(Volume::fromMm3(20.0));
    }

    public function test_multiply(): void
    {
        $result = Volume::fromMm3(200.0)->multiply(1.5);

        $this->assertEqualsWithDelta(300.0, $result->mm3(), 1e-9);
    }

    public function test_divide(): void
    {
        $result = Volume::fromMm3(200.0)->divide(2.0);

        $this->assertSame(100.0, $result->mm3());
    }

    public function test_divide_by_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Volume::fromMm3(200.0)->divide(0.0);
    }

    public function test_equals_within_epsilon(): void
    {
        $this->assertTrue(Volume::fromMm3(100.0)->equals(Volume::fromMm3(100.0 + 1e-10)));
        $this->assertFalse(Volume::fromMm3(100.0)->equals(Volume::fromMm3(100.1)));
    }

    public function test_greater_than_and_less_than(): void
    {
        $bigger = Volume::fromMm3(200.0);
        $smaller = Volume::fromMm3(100.0);

        $this->assertTrue($bigger->greaterThan($smaller));
        $this->assertTrue($smaller->lessThan($bigger));
        $this->assertFalse($smaller->greaterThan($bigger));
    }

    public function test_is_immutable(): void
    {
        $original = Volume::fromMm3(100.0);
        $original->add(Volume::fromMm3(50.0));

        $this->assertSame(100.0, $original->mm3());
    }

    public function test_to_string(): void
    {
        $this->assertSame('100.0000 mm³', (string) Volume::fromMm3(100.0));
    }

    public function test_divided_by_area(): void
    {
        // t = 2V/A: сфероид-заглушка — V=2000 мм³, A=100 мм² -> толщина 20 мм на сторону расчёта
        $thickness = Volume::fromMm3(2000.0)->dividedByArea(Area::fromMm2(100.0));

        $this->assertEqualsWithDelta(20.0, $thickness->mm(), 1e-9);
    }

    public function test_divided_by_zero_area_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Volume::fromMm3(2000.0)->dividedByArea(Area::zero());
    }
}