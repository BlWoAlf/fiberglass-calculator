<?php

namespace Domain\Layup;

use InvalidArgumentException;

final readonly class HardenerCurvePoint
{
    public function __construct(
        public float $temperatureC,
        public float $ratio,
    ) {
        if (! is_finite($temperatureC)) {
            throw new InvalidArgumentException("Temperature must be a finite number, got {$temperatureC}.");
        }

        if (! is_finite($ratio) || $ratio < 0) {
            throw new InvalidArgumentException("Hardener ratio cannot be negative, got {$ratio}.");
        }
    }
}