<?php

namespace Domain\Layup;

use InvalidArgumentException;

final readonly class Process
{
    /** @var HardenerCurvePoint[] */
    private array $hardenerCurve;

    public function __construct(
        public float $resinRatio,
        public float $wasteFactor,
        array $hardenerCurve,
    ) {
        if (! is_finite($resinRatio) || $resinRatio < 0) {
            throw new InvalidArgumentException("Resin ratio cannot be negative, got {$resinRatio}.");
        }

        if (! is_finite($wasteFactor) || $wasteFactor < 0) {
            throw new InvalidArgumentException("Waste factor cannot be negative, got {$wasteFactor}.");
        }

        if ($hardenerCurve === []) {
            throw new InvalidArgumentException('Hardener curve must have at least one point.');
        }

        foreach ($hardenerCurve as $point) {
            if (! $point instanceof HardenerCurvePoint) {
                throw new InvalidArgumentException('Hardener curve must contain only HardenerCurvePoint instances.');
            }
        }

        usort($hardenerCurve, fn (HardenerCurvePoint $a, HardenerCurvePoint $b) => $a->temperatureC <=> $b->temperatureC);

        for ($i = 1; $i < count($hardenerCurve); $i++) {
            if ($hardenerCurve[$i]->temperatureC === $hardenerCurve[$i - 1]->temperatureC) {
                throw new InvalidArgumentException(
                    "Hardener curve cannot have duplicate temperature points, got {$hardenerCurve[$i]->temperatureC}°C twice."
                );
            }
        }

        $this->hardenerCurve = $hardenerCurve;
    }

    public function hardenerRatio(float $temperatureC): float
    {
        if (! is_finite($temperatureC)) {
            throw new InvalidArgumentException("Temperature must be a finite number, got {$temperatureC}.");
        }

        $first = $this->hardenerCurve[0];

        if ($temperatureC <= $first->temperatureC) {
            return $first->ratio;
        }

        $last = $this->hardenerCurve[count($this->hardenerCurve) - 1];

        if ($temperatureC >= $last->temperatureC) {
            return $last->ratio;
        }

        for ($i = 0; $i < count($this->hardenerCurve) - 1; $i++) {
            $current = $this->hardenerCurve[$i];
            $next = $this->hardenerCurve[$i + 1];

            if ($temperatureC >= $current->temperatureC && $temperatureC <= $next->temperatureC) {
                $progress = ($temperatureC - $current->temperatureC) / ($next->temperatureC - $current->temperatureC);

                return $current->ratio + ($next->ratio - $current->ratio) * $progress;
            }
        }

        throw new \LogicException('Unreachable: temperature not bracketed by hardener curve points.');
    }
}