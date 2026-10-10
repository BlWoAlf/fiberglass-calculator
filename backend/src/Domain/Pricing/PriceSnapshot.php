<?php

namespace Domain\Pricing;

use Domain\ValueObject\Money;
use InvalidArgumentException;

final readonly class PriceSnapshot
{
    private string $currency;

    /** @var array<int, Money> material id -> price per kilogram */
    private array $pricesByMaterialId;

    public function __construct(array $pricesByMaterialId)
    {
        if ($pricesByMaterialId === []) {
            throw new InvalidArgumentException('PriceSnapshot must contain at least one price.');
        }

        $currency = null;

        foreach ($pricesByMaterialId as $materialId => $price) {
            if (! is_int($materialId) || $materialId <= 0) {
                throw new InvalidArgumentException("Material id must be a positive integer, got \"{$materialId}\".");
            }

            if (! $price instanceof Money) {
                throw new InvalidArgumentException("Price for material id {$materialId} must be a Money instance.");
            }

            $currency ??= $price->currency();

            if ($price->currency() !== $currency) {
                throw new InvalidArgumentException(
                    "All prices in a PriceSnapshot must share the same currency, got \"{$price->currency()}\" and \"{$currency}\"."
                );
            }
        }

        $this->currency = $currency;
        $this->pricesByMaterialId = $pricesByMaterialId;
    }

    public function pricePerKgFor(int $materialId): Money
    {
        if (! array_key_exists($materialId, $this->pricesByMaterialId)) {
            throw new InvalidArgumentException("No price found for material id {$materialId}.");
        }

        return $this->pricesByMaterialId[$materialId];
    }

    public function currency(): string
    {
        return $this->currency;
    }
}
