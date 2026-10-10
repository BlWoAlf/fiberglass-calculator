<?php

namespace Tests\Unit\Domain\Pricing;

use Domain\Pricing\PriceSnapshot;
use Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PriceSnapshotTest extends TestCase
{
    public function test_creates_with_valid_prices(): void
    {
        $snapshot = new PriceSnapshot([
            1 => Money::fromMajorUnits(450.0, 'RUB'),
            2 => Money::fromMajorUnits(900.0, 'RUB'),
        ]);

        $this->assertSame('RUB', $snapshot->currency());
        $this->assertTrue($snapshot->pricePerKgFor(1)->equals(Money::fromMajorUnits(450.0, 'RUB')));
        $this->assertTrue($snapshot->pricePerKgFor(2)->equals(Money::fromMajorUnits(900.0, 'RUB')));
    }

    public function test_rejects_empty_snapshot(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PriceSnapshot([]);
    }

    public function test_rejects_non_positive_material_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PriceSnapshot([0 => Money::fromMajorUnits(100.0, 'RUB')]);
    }

    public function test_rejects_non_money_price(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PriceSnapshot([1 => 100.0]);
    }

    public function test_rejects_mixed_currencies(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PriceSnapshot([
            1 => Money::fromMajorUnits(450.0, 'RUB'),
            2 => Money::fromMajorUnits(12.0, 'USD'),
        ]);
    }

    public function test_price_for_unknown_material_throws(): void
    {
        $snapshot = new PriceSnapshot([1 => Money::fromMajorUnits(450.0, 'RUB')]);

        $this->expectException(InvalidArgumentException::class);

        $snapshot->pricePerKgFor(999);
    }
}
