<?php

namespace Tests\Unit\Domain\ValueObject;

use Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_creates_from_minor_units(): void
    {
        $money = Money::fromMinorUnits(15050, 'RUB');

        $this->assertSame(15050, $money->minorUnits());
        $this->assertSame('RUB', $money->currency());
    }

    public function test_creates_from_major_units(): void
    {
        $money = Money::fromMajorUnits(150.5, 'RUB');

        $this->assertSame(15050, $money->minorUnits());
        $this->assertSame(150.5, $money->majorUnits());
    }

    public function test_from_major_units_rounds_to_nearest_minor_unit(): void
    {
        $money = Money::fromMajorUnits(10.005, 'RUB');

        $this->assertSame(1001, $money->minorUnits());
    }

    public function test_zero(): void
    {
        $money = Money::zero('RUB');

        $this->assertTrue($money->isZero());
        $this->assertSame('RUB', $money->currency());
    }

    public function test_rejects_lowercase_currency(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::zero('rub');
    }

    public function test_rejects_currency_with_wrong_length(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::zero('RU');
    }

    public function test_rejects_non_finite_major_units(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromMajorUnits(NAN, 'RUB');
    }

    public function test_allows_negative_amount(): void
    {
        // дельта сравнения вариантов расчёта (/api/calculations/compare), не абсолютная стоимость
        $money = Money::fromMajorUnits(-42.0, 'RUB');

        $this->assertSame(-4200, $money->minorUnits());
    }

    public function test_add_same_currency(): void
    {
        $result = Money::fromMinorUnits(100, 'RUB')->add(Money::fromMinorUnits(50, 'RUB'));

        $this->assertSame(150, $result->minorUnits());
    }

    public function test_add_different_currency_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromMinorUnits(100, 'RUB')->add(Money::fromMinorUnits(50, 'USD'));
    }

    public function test_subtract_same_currency(): void
    {
        $result = Money::fromMinorUnits(100, 'RUB')->subtract(Money::fromMinorUnits(40, 'RUB'));

        $this->assertSame(60, $result->minorUnits());
    }

    public function test_subtract_can_go_negative(): void
    {
        $result = Money::fromMinorUnits(10, 'RUB')->subtract(Money::fromMinorUnits(20, 'RUB'));

        $this->assertSame(-10, $result->minorUnits());
    }

    public function test_subtract_different_currency_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromMinorUnits(100, 'RUB')->subtract(Money::fromMinorUnits(50, 'USD'));
    }

    public function test_multiply(): void
    {
        $result = Money::fromMinorUnits(1000, 'RUB')->multiply(1.5);

        $this->assertSame(1500, $result->minorUnits());
    }

    public function test_multiply_rounds_to_nearest_minor_unit(): void
    {
        $result = Money::fromMinorUnits(100, 'RUB')->multiply(1.006);

        $this->assertSame(101, $result->minorUnits());
    }

    public function test_multiply_by_negative_factor(): void
    {
        $result = Money::fromMinorUnits(1000, 'RUB')->multiply(-1.0);

        $this->assertSame(-1000, $result->minorUnits());
    }

    public function test_is_zero(): void
    {
        $this->assertTrue(Money::fromMinorUnits(0, 'RUB')->isZero());
        $this->assertFalse(Money::fromMinorUnits(1, 'RUB')->isZero());
    }

    public function test_greater_than_and_less_than(): void
    {
        $bigger = Money::fromMinorUnits(200, 'RUB');
        $smaller = Money::fromMinorUnits(100, 'RUB');

        $this->assertTrue($bigger->greaterThan($smaller));
        $this->assertTrue($smaller->lessThan($bigger));
        $this->assertFalse($smaller->greaterThan($bigger));
    }

    public function test_greater_than_different_currency_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromMinorUnits(200, 'RUB')->greaterThan(Money::fromMinorUnits(100, 'USD'));
    }

    public function test_equals_same_amount_and_currency(): void
    {
        $this->assertTrue(Money::fromMinorUnits(100, 'RUB')->equals(Money::fromMinorUnits(100, 'RUB')));
    }

    public function test_equals_is_exact_no_epsilon(): void
    {
        $this->assertFalse(Money::fromMinorUnits(100, 'RUB')->equals(Money::fromMinorUnits(101, 'RUB')));
    }

    public function test_equals_different_currency_is_false_not_exception(): void
    {
        $this->assertFalse(Money::fromMinorUnits(100, 'RUB')->equals(Money::fromMinorUnits(100, 'USD')));
    }

    public function test_sum(): void
    {
        $total = Money::sum(
            Money::fromMinorUnits(100, 'RUB'),
            Money::fromMinorUnits(250, 'RUB'),
            Money::fromMinorUnits(50, 'RUB'),
        );

        $this->assertSame(400, $total->minorUnits());
        $this->assertSame('RUB', $total->currency());
    }

    public function test_sum_of_mismatched_currencies_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::sum(
            Money::fromMinorUnits(100, 'RUB'),
            Money::fromMinorUnits(50, 'USD'),
        );
    }

    public function test_sum_of_empty_list_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::sum();
    }

    public function test_is_immutable(): void
    {
        $original = Money::fromMinorUnits(100, 'RUB');
        $original->add(Money::fromMinorUnits(50, 'RUB'));

        $this->assertSame(100, $original->minorUnits());
    }

    public function test_to_string(): void
    {
        $this->assertSame('150.50 RUB', (string) Money::fromMinorUnits(15050, 'RUB'));
    }
}