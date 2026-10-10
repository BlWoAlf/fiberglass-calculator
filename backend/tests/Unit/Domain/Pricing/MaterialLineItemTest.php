<?php

namespace Tests\Unit\Domain\Pricing;

use Domain\Pricing\MaterialLineItem;
use Domain\ValueObject\Mass;
use Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MaterialLineItemTest extends TestCase
{
    public function test_creates_with_valid_data(): void
    {
        $line = new MaterialLineItem(
            materialId: 1,
            materialName: 'Стекломат 450',
            mass: Mass::fromGrams(600.0),
            cost: Money::fromMajorUnits(270.0, 'RUB'),
        );

        $this->assertSame(1, $line->materialId);
        $this->assertSame('Стекломат 450', $line->materialName);
        $this->assertSame(600.0, $line->mass->g());
    }

    public function test_rejects_non_positive_material_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MaterialLineItem(0, 'Material', Mass::zero(), Money::zero('RUB'));
    }

    public function test_rejects_empty_material_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MaterialLineItem(1, '', Mass::zero(), Money::zero('RUB'));
    }
}