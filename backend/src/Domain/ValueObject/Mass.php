<?php

namespace Domain\ValueObject;

use InvalidArgumentException;

final readonly class Mass
{
    private const float EPSILON = 1e-9;

    private const float G_PER_KG = 1_000.0;

    private function __construct(private float $valueG)
    {
        if (! is_finite($valueG)) {
            throw new InvalidArgumentException("Mass must be a finite number, got {$valueG}.");
        }

        if ($valueG < 0) {
            throw new InvalidArgumentException("Mass cannot be negative, got {$valueG} g.");
        }
    }

    public static function fromGrams(float $g): self
    {
        return new self($g);
    }

    public static function fromKilograms(float $kg): self
    {
        return new self($kg * self::G_PER_KG);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public static function sum(self ...$masses): self
    {
        $totalG = 0.0;

        foreach ($masses as $mass) {
            $totalG += $mass->valueG;
        }

        return new self($totalG);
    }

    public function g(): float
    {
        return $this->valueG;
    }

    public function kg(): float
    {
        return $this->valueG / self::G_PER_KG;
    }

    public function add(self $other): self
    {
        return new self($this->valueG + $other->valueG);
    }

    public function subtract(self $other): self
    {
        return new self($this->valueG - $other->valueG);
    }

    public function multiply(float $factor): self
    {
        return new self($this->valueG * $factor);
    }

    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            throw new InvalidArgumentException('Cannot divide Mass by zero.');
        }

        return new self($this->valueG / $divisor);
    }

    public function isZero(): bool
    {
        return abs($this->valueG) < self::EPSILON;
    }

    public function greaterThan(self $other): bool
    {
        return $this->valueG > $other->valueG + self::EPSILON;
    }

    public function lessThan(self $other): bool
    {
        return $this->valueG < $other->valueG - self::EPSILON;
    }

    public function equals(self $other, float $epsilon = self::EPSILON): bool
    {
        return abs($this->valueG - $other->valueG) < $epsilon;
    }

    public function __toString(): string
    {
        return sprintf('%.4f g', $this->valueG);
    }
}
