<?php

namespace Domain\Layup;

use Domain\ValueObject\Area;
use Domain\ValueObject\Mass;
use InvalidArgumentException;

final readonly class LayupLayer
{
    public function __construct(
        public MaterialRef $material,
        public float $coverage,
        public float $overlapWaste,
        public int $position,
    ) {
        if (! is_finite($coverage) || $coverage < 0 || $coverage > 1) {
            throw new InvalidArgumentException("Coverage must be between 0 and 1, got {$coverage}.");
        }

        if (! is_finite($overlapWaste) || $overlapWaste < 0 || $overlapWaste > 1) {
            throw new InvalidArgumentException("Overlap waste must be between 0 and 1, got {$overlapWaste}.");
        }

        if ($position < 0) {
            throw new InvalidArgumentException("Position cannot be negative, got {$position}.");
        }
    }

    public function massFor(Area $effectiveArea): Mass
    {
        $layerArea = $effectiveArea
            ->multiply($this->coverage)
            ->multiply(1 + $this->overlapWaste);

        return $layerArea->toMass($this->material->arealDensityGsm);
    }
}