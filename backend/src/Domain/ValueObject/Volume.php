<?php

namespace Domain\ValueObject;

use InvalidArgumentException;

final readonly class Volume
{
    private const float EPSILON = 1e-9;

    private const float MM3_PER_CM3 = 1_000.0;

    private const float MM3_PER_M3 = 1_000_000_000.0;

    private const float MM3_PER_LITER = 1_000_000.0;

    private function __construct(private float $valueMm3)
    {
        if (! is_finite($valueMm3)) {
            throw new InvalidArgumentException("Volume must be a finite number, got {$valueMm3}.");
        }

        if ($valueMm3 < 0) {
            throw new InvalidArgumentException("Volume cannot be negative, got {$valueMm3} mm³.");
        }
    }

    public static function fromMm3(float $mm3): self
    {
        return new self($mm3);
    }

    public static function fromCm3(float $cm3): self
    {
        return new self($cm3 * self::MM3_PER_CM3);
    }

    public static function fromM3(float $m3): self
    {
        return new self($m3 * self::MM3_PER_M3);
    }

    public static function fromLiters(float $liters): self
    {
        return new self($liters * self::MM3_PER_LITER);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function mm3(): float
    {
        return $this->valueMm3;
    }

    public function cm3(): float
    {
        return $this->valueMm3 / self::MM3_PER_CM3;
    }

    public function m3(): float
    {
        return $this->valueMm3 / self::MM3_PER_M3;
    }

    public function liters(): float
    {
        return $this->valueMm3 / self::MM3_PER_LITER;
    }

    public function add(self $other): self
    {
        return new self($this->valueMm3 + $other->valueMm3);
    }

    public function subtract(self $other): self
    {
        return new self($this->valueMm3 - $other->valueMm3);
    }

    public function multiply(float $factor): self
    {
        return new self($this->valueMm3 * $factor);
    }

    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            throw new InvalidArgumentException('Cannot divide Volume by zero.');
        }

        return new self($this->valueMm3 / $divisor);
    }

    public function dividedByArea(Area $area): Length
    {
        if ($area->isZero()) {
            throw new InvalidArgumentException('Cannot divide Volume by a zero Area.');
        }

        return Length::fromMm($this->valueMm3 / $area->mm2());
    }

    public function isZero(): bool
    {
        return abs($this->valueMm3) < self::EPSILON;
    }

    public function greaterThan(self $other): bool
    {
        return $this->valueMm3 > $other->valueMm3 + self::EPSILON;
    }

    public function lessThan(self $other): bool
    {
        return $this->valueMm3 < $other->valueMm3 - self::EPSILON;
    }

    public function equals(self $other, float $epsilon = self::EPSILON): bool
    {
        return abs($this->valueMm3 - $other->valueMm3) < $epsilon;
    }

    public function __toString(): string
    {
        return sprintf('%.4f mm³', $this->valueMm3);
    }
}
