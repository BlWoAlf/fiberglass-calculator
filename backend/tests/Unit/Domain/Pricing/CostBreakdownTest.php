<?php

namespace Tests\Unit\Domain\Pricing;

use Domain\Pricing\CostBreakdown;
use Domain\Pricing\MaterialLineItem;
use Domain\ValueObject\Mass;
use Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CostBreakdownTest extends TestCase
{
    /** @return MaterialLineItem[] */
    private function reinforcementLines(): array
    {
        return [
            new MaterialLineItem(1, 'Стекломат 450', Mass::fromGrams(600.0), Money::fromMajorUnits(270.0, 'RUB')),
            new MaterialLineItem(2, 'Стеклоровинг', Mass::fromGrams(900.0), Money::fromMajorUnits(450.0, 'RUB')),
        ];
    }

    public function test_creates_with_consistent_totals(): void
    {
        $breakdown = new CostBreakdown(
            reinforcementLines: $this->reinforcementLines(),
            massResin: Mass::fromGrams(2250.0),
            costResin: Money::fromMajorUnits(900.0, 'RUB'),
            massHardener: Mass::fromGrams(675.0),
            costHardener: Money::fromMajorUnits(300.0, 'RUB'),
            massGelcoat: null,
            costGelcoat: null,
            wasteLine: Money::fromMajorUnits(150.0, 'RUB'),
            massTotal: Mass::fromGrams(4425.0),
            totalCost: Money::fromMajorUnits(2070.0, 'RUB'),
        );

        $this->assertCount(2, $breakdown->reinforcementLines);
        $this->assertSame(4425.0, $breakdown->massTotal->g());
        $this->assertSame(207000, $breakdown->totalCost->minorUnits());
    }

    public function test_creates_with_gelcoat(): void
    {
        $breakdown = new CostBreakdown(
            reinforcementLines: $this->reinforcementLines(),
            massResin: Mass::fromGrams(2250.0),
            costResin: Money::fromMajorUnits(900.0, 'RUB'),
            massHardener: Mass::fromGrams(675.0),
            costHardener: Money::fromMajorUnits(300.0, 'RUB'),
            massGelcoat: Mass::fromGrams(500.0),
            costGelcoat: Money::fromMajorUnits(200.0, 'RUB'),
            wasteLine: Money::fromMajorUnits(150.0, 'RUB'),
            massTotal: Mass::fromGrams(4925.0),
            totalCost: Money::fromMajorUnits(2270.0, 'RUB'),
        );

        $this->assertSame(4925.0, $breakdown->massTotal->g());
    }

    public function test_rejects_empty_reinforcement_lines(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CostBreakdown(
            reinforcementLines: [],
            massResin: Mass::zero(),
            costResin: Money::zero('RUB'),
            massHardener: Mass::zero(),
            costHardener: Money::zero('RUB'),
            massGelcoat: null,
            costGelcoat: null,
            wasteLine: Money::zero('RUB'),
            massTotal: Mass::zero(),
            totalCost: Money::zero('RUB'),
        );
    }

    public function test_rejects_non_material_line_item_entries(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CostBreakdown(
            reinforcementLines: ['not a line item'],
            massResin: Mass::zero(),
            costResin: Money::zero('RUB'),
            massHardener: Mass::zero(),
            costHardener: Money::zero('RUB'),
            massGelcoat: null,
            costGelcoat: null,
            wasteLine: Money::zero('RUB'),
            massTotal: Mass::zero(),
            totalCost: Money::zero('RUB'),
        );
    }

    public function test_rejects_mismatched_mass_total(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CostBreakdown(
            reinforcementLines: $this->reinforcementLines(),
            massResin: Mass::fromGrams(2250.0),
            costResin: Money::fromMajorUnits(900.0, 'RUB'),
            massHardener: Mass::fromGrams(675.0),
            costHardener: Money::fromMajorUnits(300.0, 'RUB'),
            massGelcoat: null,
            costGelcoat: null,
            wasteLine: Money::fromMajorUnits(150.0, 'RUB'),
            massTotal: Mass::fromGrams(9999.0), // заведомо неверная сумма
            totalCost: Money::fromMajorUnits(2070.0, 'RUB'),
        );
    }

    public function test_rejects_mismatched_total_cost(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CostBreakdown(
            reinforcementLines: $this->reinforcementLines(),
            massResin: Mass::fromGrams(2250.0),
            costResin: Money::fromMajorUnits(900.0, 'RUB'),
            massHardener: Mass::fromGrams(675.0),
            costHardener: Money::fromMajorUnits(300.0, 'RUB'),
            massGelcoat: null,
            costGelcoat: null,
            wasteLine: Money::fromMajorUnits(150.0, 'RUB'),
            massTotal: Mass::fromGrams(4425.0),
            totalCost: Money::fromMajorUnits(999999.0, 'RUB'), // заведомо неверная сумма
        );
    }

    public function test_rejects_gelcoat_mass_without_gelcoat_cost(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CostBreakdown(
            reinforcementLines: $this->reinforcementLines(),
            massResin: Mass::fromGrams(2250.0),
            costResin: Money::fromMajorUnits(900.0, 'RUB'),
            massHardener: Mass::fromGrams(675.0),
            costHardener: Money::fromMajorUnits(300.0, 'RUB'),
            massGelcoat: Mass::fromGrams(500.0),
            costGelcoat: null,
            wasteLine: Money::fromMajorUnits(150.0, 'RUB'),
            massTotal: Mass::fromGrams(4925.0),
            totalCost: Money::fromMajorUnits(2070.0, 'RUB'),
        );
    }

    public function test_rejects_mixed_currencies(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CostBreakdown(
            reinforcementLines: $this->reinforcementLines(),
            massResin: Mass::fromGrams(2250.0),
            costResin: Money::fromMajorUnits(900.0, 'USD'), // другая валюта
            massHardener: Mass::fromGrams(675.0),
            costHardener: Money::fromMajorUnits(300.0, 'RUB'),
            massGelcoat: null,
            costGelcoat: null,
            wasteLine: Money::fromMajorUnits(150.0, 'RUB'),
            massTotal: Mass::fromGrams(4425.0),
            totalCost: Money::fromMajorUnits(2070.0, 'RUB'),
        );
    }
}