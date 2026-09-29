<?php

namespace Domain\ValueObject;

use InvalidArgumentException;

final readonly class Length
{
    private const float EPSILON = 1e-9;

    private const float MM_PER_CM = 10.0;

    private const float MM_PER_M = 1_000.0;

    private function __construct(private float $valueMm)
    {
        if (! is_finite($valueMm)) {
            throw new InvalidArgumentException("Length must be a finite number, got {$valueMm}.");
        }

        if ($valueMm < 0) {
            throw new InvalidArgumentException("Length cannot be negative, got {$valueMm} mm.");
        }
    }

    public static function fromMm(float $mm): self
    {
        return new self($mm);
    }

    public static function fromCm(float $cm): self
    {
        return new self($cm * self::MM_PER_CM);
    }

    public static function fromM(float $m): self
    {
        return new self($m * self::MM_PER_M);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function mm(): float
    {
        return $this->valueMm;
    }

    public function cm(): float
    {
        return $this->valueMm / self::MM_PER_CM;
    }

    public function m(): float
    {
        return $this->valueMm / self::MM_PER_M;
    }

    public function add(self $other): self
    {
        return new self($this->valueMm + $other->valueMm);
    }

    public function subtract(self $other): self
    {
        return new self($this->valueMm - $other->valueMm);
    }

    public function multiply(float $factor): self
    {
        return new self($this->valueMm * $factor);
    }

    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            throw new InvalidArgumentException('Cannot divide Length by zero.');
        }

        return new self($this->valueMm / $divisor);
    }

    public function ratioTo(self $other): float
    {
        if ($other->isZero()) {
            throw new InvalidArgumentException('Cannot compute ratio against a zero Length.');
        }

        return $this->valueMm / $other->valueMm;
    }

    public function isZero(): bool
    {
        return abs($this->valueMm) < self::EPSILON;
    }

    public function greaterThan(self $other): bool
    {
        return $this->valueMm > $other->valueMm + self::EPSILON;
    }

    public function lessThan(self $other): bool
    {
        return $this->valueMm < $other->valueMm - self::EPSILON;
    }

    public function equals(self $other, float $epsilon = self::EPSILON): bool
    {
        return abs($this->valueMm - $other->valueMm) < $epsilon;
    }

    public function __toString(): string
    {
        return sprintf('%.4f mm', $this->valueMm);
    }
}