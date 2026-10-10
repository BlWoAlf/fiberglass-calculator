<?php

namespace Domain\Layup;

use Domain\Pricing\CostBreakdown;
use Domain\Pricing\MaterialLineItem;
use Domain\Pricing\PriceSnapshot;
use Domain\ValueObject\Area;
use Domain\ValueObject\Mass;
use Domain\ValueObject\Money;

final class LayupCalculator
{
    private const float DEFAULT_TEMPERATURE_C = 20.0;

    public function calculate(
        Area $effectiveArea,
        LayupScheme $scheme,
        Process $process,
        MaterialRef $resin,
        MaterialRef $hardener,
        PriceSnapshot $prices,
        ?MaterialRef $gelcoat = null,
        ?float $temperatureC = null,
    ): CostBreakdown {
        $reinforcementLines = array_map(
            fn (LayupLayer $layer) => $this->reinforcementLine($layer, $effectiveArea, $prices),
            $scheme->layers(),
        );

        $massResin = $scheme->totalReinforcementMass($effectiveArea)->multiply($process->resinRatio);
        $costResin = $prices->pricePerKgFor($resin->id)->multiply($massResin->kg());

        $temperature = $temperatureC ?? self::DEFAULT_TEMPERATURE_C;
        $massHardener = $massResin->multiply($process->hardenerRatio($temperature));
        $costHardener = $prices->pricePerKgFor($hardener->id)->multiply($massHardener->kg());

        $massGelcoat = null;
        $costGelcoat = null;

        if ($gelcoat !== null) {
            $massGelcoat = $effectiveArea->toMass($gelcoat->arealDensityGsm);
            $costGelcoat = $prices->pricePerKgFor($gelcoat->id)->multiply($massGelcoat->kg());
        }

        $masses = array_map(static fn (MaterialLineItem $line) => $line->mass, $reinforcementLines);
        $masses[] = $massResin;
        $masses[] = $massHardener;
        $masses[] = $massGelcoat ?? Mass::zero();
        $massTotal = Mass::sum(...$masses);

        $costs = array_map(static fn (MaterialLineItem $line) => $line->cost, $reinforcementLines);
        $costs[] = $costResin;
        $costs[] = $costHardener;
        $costs[] = $costGelcoat ?? Money::zero($prices->currency());
        $costBeforeWaste = Money::sum(...$costs);

        $wasteLine = $costBeforeWaste->multiply($process->wasteFactor);
        $totalCost = $costBeforeWaste->add($wasteLine);

        return new CostBreakdown(
            reinforcementLines: $reinforcementLines,
            massResin: $massResin,
            costResin: $costResin,
            massHardener: $massHardener,
            costHardener: $costHardener,
            massGelcoat: $massGelcoat,
            costGelcoat: $costGelcoat,
            wasteLine: $wasteLine,
            massTotal: $massTotal,
            totalCost: $totalCost,
        );
    }

    private function reinforcementLine(LayupLayer $layer, Area $effectiveArea, PriceSnapshot $prices): MaterialLineItem
    {
        $mass = $layer->massFor($effectiveArea);
        $cost = $prices->pricePerKgFor($layer->material->id)->multiply($mass->kg());

        return new MaterialLineItem(
            materialId: $layer->material->id,
            materialName: $layer->material->name,
            mass: $mass,
            cost: $cost,
        );
    }
}