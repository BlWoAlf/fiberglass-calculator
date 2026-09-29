<?php

namespace Tests\Unit\Domain\ValueObject;

use Domain\ValueObject\Mass;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MassTest extends TestCase
{
    public function test_creates_from_grams(): void
    {
        $mass = Mass::fromGrams(150.0);

        $this->assertSame(150.0, $mass->g());
    }

    public function test_creates_from_kilograms(): void
    {
        $mass = Mass::fromKilograms(2.5);

        $this->assertSame(2_500.0, $mass->g());
        $this->assertSame(2.5, $mass->kg());
    }

    public function test_zero_is_zero(): void
    {
        $this->assertTrue(Mass::zero()->isZero());
    }

    public function test_rejects_negative_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Mass::fromGrams(-1.0);
    }

    public function test_rejects_non_finite_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Mass::fromGrams(NAN);
    }

    public function test_sum(): void
    {
        $total = Mass::sum(
            Mass::fromGrams(100.0),
            Mass::fromGrams(250.0),
            Mass::fromGrams(50.0),
        );

        $this->assertSame(400.0, $total->g());
    }

    public function test_sum_of_no_masses_is_zero(): void
    {
        $this->assertTrue(Mass::sum()->isZero());
    }

    public function test_add(): void
    {
        $result = Mass::fromGrams(100.0)->add(Mass::fromGrams(50.0));

        $this->assertSame(150.0, $result->g());
    }

    public function test_subtract(): void
    {
        $result = Mass::fromGrams(100.0)->subtract(Mass::fromGrams(40.0));

        $this->assertSame(60.0, $result->g());
    }

    public function test_subtract_below_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Mass::fromGrams(10.0)->subtract(Mass::fromGrams(20.0));
    }

    public function test_multiply(): void
    {
        // resin_ratio: армирование -> масса смолы
        $result = Mass::fromGrams(1000.0)->multiply(1.5);

        $this->assertEqualsWithDelta(1500.0, $result->g(), 1e-9);
    }

    public function test_divide(): void
    {
        $result = Mass::fromGrams(200.0)->divide(2.0);

        $this->assertSame(100.0, $result->g());
    }

    public function test_divide_by_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Mass::fromGrams(200.0)->divide(0.0);
    }

    public function test_equals_within_epsilon(): void
    {
        $this->assertTrue(Mass::fromGrams(100.0)->equals(Mass::fromGrams(100.0 + 1e-10)));
        $this->assertFalse(Mass::fromGrams(100.0)->equals(Mass::fromGrams(100.1)));
    }

    public function test_greater_than_and_less_than(): void
    {
        $bigger = Mass::fromGrams(200.0);
        $smaller = Mass::fromGrams(100.0);

        $this->assertTrue($bigger->greaterThan($smaller));
        $this->assertTrue($smaller->lessThan($bigger));
        $this->assertFalse($smaller->greaterThan($bigger));
    }

    public function test_is_immutable(): void
    {
        $original = Mass::fromGrams(100.0);
        $original->add(Mass::fromGrams(50.0));

        $this->assertSame(100.0, $original->g());
    }

    public function test_to_string(): void
    {
        $this->assertSame('100.0000 g', (string) Mass::fromGrams(100.0));
    }
}
