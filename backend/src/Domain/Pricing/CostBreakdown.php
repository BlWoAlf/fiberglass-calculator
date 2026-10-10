<?php

namespace Domain\Pricing;

use Domain\ValueObject\Mass;
use Domain\ValueObject\Money;
use InvalidArgumentException;

final readonly class CostBreakdown
{
    /** @var MaterialLineItem[] */
    public array $reinforcementLines;

    public function __construct(
        array $reinforcementLines,
        public Mass $massResin,
        public Money $costResin,
        public Mass $massHardener,
        public Money $costHardener,
        public ?Mass $massGelcoat,
        public ?Money $costGelcoat,
        public Money $wasteLine,
        public Mass $massTotal,
        public Money $totalCost,
    ) {
        if ($reinforcementLines === []) {
            throw new InvalidArgumentException('CostBreakdown must have at least one reinforcement line.');
        }

        foreach ($reinforcementLines as $line) {
            if (! $line instanceof MaterialLineItem) {
                throw new InvalidArgumentException('reinforcementLines must contain only MaterialLineItem instances.');
            }
        }

        if (($massGelcoat === null) !== ($costGelcoat === null)) {
            throw new InvalidArgumentException('massGelcoat and costGelcoat must both be null or both be set.');
        }

        $this->reinforcementLines = $reinforcementLines;

        $masses = array_map(static fn (MaterialLineItem $line) => $line->mass, $reinforcementLines);
        $masses[] = $massResin;
        $masses[] = $massHardener;
        $masses[] = $massGelcoat ?? Mass::zero();

        $expectedMassTotal = Mass::sum(...$masses);

        if (! $expectedMassTotal->equals($massTotal)) {
            throw new InvalidArgumentException(
                "massTotal ({$massTotal}) does not match the sum of its components ({$expectedMassTotal})."
            );
        }

        $costs = array_map(static fn (MaterialLineItem $line) => $line->cost, $reinforcementLines);
        $costs[] = $costResin;
        $costs[] = $costHardener;
        $costs[] = $costGelcoat ?? Money::zero($totalCost->currency());
        $costs[] = $wasteLine;

        $expectedTotalCost = Money::sum(...$costs);

        if (! $expectedTotalCost->equals($totalCost)) {
            throw new InvalidArgumentException(
                "totalCost ({$totalCost}) does not match the sum of its components ({$expectedTotalCost})."
            );
        }
    }
}