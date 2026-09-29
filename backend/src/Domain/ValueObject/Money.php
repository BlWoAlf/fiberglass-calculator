<?php

namespace Domain\ValueObject;

use InvalidArgumentException;

final readonly class Money
{
    private const int MINOR_UNITS_PER_MAJOR_UNIT = 100;

    private function __construct(private int $minorUnits, private string $currency)
    {
        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException("Currency must be a 3-letter uppercase ISO 4217 code, got \"{$currency}\".");
        }
    }

    public static function fromMinorUnits(int $minorUnits, string $currency): self
    {
        return new self($minorUnits, $currency);
    }

    public static function fromMajorUnits(float $majorUnits, string $currency): self
    {
        if (! is_finite($majorUnits)) {
            throw new InvalidArgumentException("Money amount must be a finite number, got {$majorUnits}.");
        }

        return new self((int) round($majorUnits * self::MINOR_UNITS_PER_MAJOR_UNIT), $currency);
    }

    public static function zero(string $currency): self
    {
        return new self(0, $currency);
    }

    public static function sum(self ...$amounts): self
    {
        if ($amounts === []) {
            throw new InvalidArgumentException('Cannot sum an empty list of Money — currency is undetermined.');
        }

        $currency = $amounts[0]->currency;
        $totalMinorUnits = 0;

        foreach ($amounts as $amount) {
            self::assertSameCurrency($currency, $amount->currency);
            $totalMinorUnits += $amount->minorUnits;
        }

        return new self($totalMinorUnits, $currency);
    }

    public function minorUnits(): int
    {
        return $this->minorUnits;
    }

    public function majorUnits(): float
    {
        return $this->minorUnits / self::MINOR_UNITS_PER_MAJOR_UNIT;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        self::assertSameCurrency($this->currency, $other->currency);

        return new self($this->minorUnits + $other->minorUnits, $this->currency);
    }

    public function subtract(self $other): self
    {
        self::assertSameCurrency($this->currency, $other->currency);

        return new self($this->minorUnits - $other->minorUnits, $this->currency);
    }

    public function multiply(float $factor): self
    {
        if (! is_finite($factor)) {
            throw new InvalidArgumentException("Factor must be a finite number, got {$factor}.");
        }

        return new self((int) round($this->minorUnits * $factor), $this->currency);
    }

    public function isZero(): bool
    {
        return $this->minorUnits === 0;
    }

    public function greaterThan(self $other): bool
    {
        self::assertSameCurrency($this->currency, $other->currency);

        return $this->minorUnits > $other->minorUnits;
    }

    public function lessThan(self $other): bool
    {
        self::assertSameCurrency($this->currency, $other->currency);

        return $this->minorUnits < $other->minorUnits;
    }

    public function equals(self $other): bool
    {
        return $this->minorUnits === $other->minorUnits && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return sprintf('%.2f %s', $this->majorUnits(), $this->currency);
    }

    private static function assertSameCurrency(string $a, string $b): void
    {
        if ($a !== $b) {
            throw new InvalidArgumentException("Currency mismatch: \"{$a}\" vs \"{$b}\".");
        }
    }
}
