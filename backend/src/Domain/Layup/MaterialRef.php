<?php

namespace Domain\Layup;

use InvalidArgumentException;

final readonly class MaterialRef
{
    public function __construct(
        public int $id,
        public string $name,
        public float $arealDensityGsm,
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException("Material id must be positive, got {$id}.");
        }

        if (trim($name) === '') {
            throw new InvalidArgumentException('Material name cannot be empty.');
        }

        if (! is_finite($arealDensityGsm) || $arealDensityGsm < 0) {
            throw new InvalidArgumentException("Areal density cannot be negative, got {$arealDensityGsm} g/m².");
        }
    }
}