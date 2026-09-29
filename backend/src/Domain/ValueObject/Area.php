<?php

namespace Domain\ValueObject;

use InvalidArgumentException;

final readonly class Area
{
    private const float EPSILON = 1e-9;

    private const float MM2_PER_M2 = 1_000_000.0;

    private function __construct(private float $valueMm2)
    {
        if (! is_finite($valueMm2)) {
            throw new InvalidArgumentException("Area must be a finite number, got {$valueMm2}.");
        }

        if ($valueMm2 < 0) {
            throw new InvalidArgumentException("Area cannot be negative, got {$valueMm2} mm².");
        }
    }

    public static function fromMm2(float $mm2): self
    {
        return new self($mm2);
    }

    public static function fromM2(float $m2): self
    {
        return new self($m2 * self::MM2_PER_M2);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function mm2(): float
    {
        return $this->valueMm2;
    }

    public function m2(): float
    {
        return $this->valueMm2 / self::MM2_PER_M2;
    }

    public function add(self $other): self
    {
        return new self($this->valueMm2 + $other->valueMm2);
    }

    public function subtract(self $other): self
    {
        return new self($this->valueMm2 - $other->valueMm2);
    }

    public function multiply(float $factor): self
    {
        return new self($this->valueMm2 * $factor);
    }

    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            throw new InvalidArgumentException('Cannot divide Area by zero.');
        }

        return new self($this->valueMm2 / $divisor);
    }

    public function isZero(): bool
    {
        return abs($this->valueMm2) < self::EPSILON;
    }

    public function greaterThan(self $other): bool
    {
        return $this->valueMm2 > $other->valueMm2 + self::EPSILON;
    }

    public function lessThan(self $other): bool
    {
        return $this->valueMm2 < $other->valueMm2 - self::EPSILON;
    }

    public function equals(self $other, float $epsilon = self::EPSILON): bool
    {
        return abs($this->valueMm2 - $other->valueMm2) < $epsilon;
    }

    public function toMass(float $arealDensityGsm): Mass
    {
        if (! is_finite($arealDensityGsm) || $arealDensityGsm < 0) {
            throw new InvalidArgumentException("Areal density cannot be negative, got {$arealDensityGsm} g/m².");
        }

        return Mass::fromGrams($this->m2() * $arealDensityGsm);
    }

    public function __toString(): string
    {
        return sprintf('%.4f mm²', $this->valueMm2);
    }
}
