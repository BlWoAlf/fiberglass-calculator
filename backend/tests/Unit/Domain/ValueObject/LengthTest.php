<?php

namespace Tests\Unit\Domain\ValueObject;

use Domain\ValueObject\Length;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LengthTest extends TestCase
{
    public function test_creates_from_mm(): void
    {
        $length = Length::fromMm(150.0);

        $this->assertSame(150.0, $length->mm());
    }

    public function test_creates_from_cm(): void
    {
        $length = Length::fromCm(62.0);

        $this->assertSame(620.0, $length->mm());
        $this->assertSame(62.0, $length->cm());
    }

    public function test_creates_from_m(): void
    {
        $length = Length::fromM(0.62);

        $this->assertEqualsWithDelta(620.0, $length->mm(), 1e-9);
        $this->assertSame(0.62, $length->m());
    }

    public function test_zero_is_zero(): void
    {
        $this->assertTrue(Length::zero()->isZero());
    }

    public function test_rejects_negative_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Length::fromMm(-1.0);
    }

    public function test_rejects_non_finite_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Length::fromMm(NAN);
    }

    public function test_add(): void
    {
        $result = Length::fromMm(100.0)->add(Length::fromMm(50.0));

        $this->assertSame(150.0, $result->mm());
    }

    public function test_subtract(): void
    {
        $result = Length::fromMm(100.0)->subtract(Length::fromMm(40.0));

        $this->assertSame(60.0, $result->mm());
    }

    public function test_subtract_below_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Length::fromMm(10.0)->subtract(Length::fromMm(20.0));
    }

    public function test_multiply(): void
    {
        $result = Length::fromMm(200.0)->multiply(1.5);

        $this->assertEqualsWithDelta(300.0, $result->mm(), 1e-9);
    }

    public function test_divide(): void
    {
        $result = Length::fromMm(200.0)->divide(2.0);

        $this->assertSame(100.0, $result->mm());
    }

    public function test_divide_by_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Length::fromMm(200.0)->divide(0.0);
    }

    public function test_ratio_to(): void
    {
        // калибровка масштаба: реальный габарит 620 мм, на скане тот же габарит вышел 310 мм
        $known = Length::fromMm(620.0);
        $measured = Length::fromMm(310.0);

        $this->assertEqualsWithDelta(2.0, $known->ratioTo($measured), 1e-9);
    }

    public function test_ratio_to_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Length::fromMm(620.0)->ratioTo(Length::zero());
    }

    public function test_equals_within_epsilon(): void
    {
        $this->assertTrue(Length::fromMm(100.0)->equals(Length::fromMm(100.0 + 1e-10)));
        $this->assertFalse(Length::fromMm(100.0)->equals(Length::fromMm(100.1)));
    }

    public function test_greater_than_and_less_than(): void
    {
        $bigger = Length::fromMm(200.0);
        $smaller = Length::fromMm(100.0);

        $this->assertTrue($bigger->greaterThan($smaller));
        $this->assertTrue($smaller->lessThan($bigger));
        $this->assertFalse($smaller->greaterThan($bigger));
    }

    public function test_is_immutable(): void
    {
        $original = Length::fromMm(100.0);
        $original->add(Length::fromMm(50.0));

        $this->assertSame(100.0, $original->mm());
    }

    public function test_to_string(): void
    {
        $this->assertSame('100.0000 mm', (string) Length::fromMm(100.0));
    }
}