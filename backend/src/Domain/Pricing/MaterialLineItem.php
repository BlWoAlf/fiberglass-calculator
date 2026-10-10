<?php

namespace Domain\Pricing;

use Domain\ValueObject\Mass;
use Domain\ValueObject\Money;
use InvalidArgumentException;

final readonly class MaterialLineItem
{
    public function __construct(
        public int $materialId,
        public string $materialName,
        public Mass $mass,
        public Money $cost,
    ) {
        if ($materialId <= 0) {
            throw new InvalidArgumentException("Material id must be positive, got {$materialId}.");
        }

        if (trim($materialName) === '') {
            throw new InvalidArgumentException('Material name cannot be empty.');
        }
    }
}