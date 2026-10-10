<?php

namespace Domain\Layup;

use Domain\ValueObject\Area;
use Domain\ValueObject\Mass;
use InvalidArgumentException;

final readonly class LayupScheme
{
    /** @var LayupLayer[] */
    private array $layers;

    public function __construct(array $layers)
    {
        if ($layers === []) {
            throw new InvalidArgumentException('LayupScheme must have at least one layer.');
        }

        foreach ($layers as $layer) {
            if (! $layer instanceof LayupLayer) {
                throw new InvalidArgumentException('LayupScheme layers must be instances of LayupLayer.');
            }
        }

        usort($layers, fn (LayupLayer $a, LayupLayer $b) => $a->position <=> $b->position);

        for ($i = 1; $i < count($layers); $i++) {
            if ($layers[$i]->position === $layers[$i - 1]->position) {
                throw new InvalidArgumentException(
                    "LayupScheme cannot have duplicate layer positions, got position {$layers[$i]->position} twice."
                );
            }
        }

        $this->layers = $layers;
    }

    /** @return LayupLayer[] */
    public function layers(): array
    {
        return $this->layers;
    }

    public function layerCount(): int
    {
        return count($this->layers);
    }

    public function totalReinforcementMass(Area $effectiveArea): Mass
    {
        return Mass::sum(...array_map(
            fn (LayupLayer $layer) => $layer->massFor($effectiveArea),
            $this->layers,
        ));
    }
}