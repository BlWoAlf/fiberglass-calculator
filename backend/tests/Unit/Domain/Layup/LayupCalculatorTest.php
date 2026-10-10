<?php

namespace Tests\Unit\Domain\Layup;

use Domain\Layup\HardenerCurvePoint;
use Domain\Layup\LayupCalculator;
use Domain\Layup\LayupLayer;
use Domain\Layup\LayupScheme;
use Domain\Layup\MaterialRef;
use Domain\Layup\Process;
use Domain\Pricing\PriceSnapshot;
use Domain\ValueObject\Area;
use Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

class LayupCalculatorTest extends TestCase
{
    private function reinforcement(): MaterialRef
    {
        return new MaterialRef(id: 1, name: 'Стекломат 450', arealDensityGsm: 450.0);
    }

    private function resin(): MaterialRef
    {
        return new MaterialRef(id: 2, name: 'Смола', arealDensityGsm: 0.0);
    }

    private function hardener(): MaterialRef
    {
        return new MaterialRef(id: 3, name: 'Отвердитель', arealDensityGsm: 0.0);
    }

    private function gelcoat(): MaterialRef
    {
        return new MaterialRef(id: 4, name: 'Гелькоут', arealDensityGsm: 500.0);
    }

    public function test_known_inputs_produce_known_result(): void
    {
        // Рассчитано вручную:
        // effective_area = 2 m²
        // слой: coverage=1.0, overlap_waste=0.0, density=450 г/м² -> mass_layer = 2 * 450 = 900 г
        // mass_reinforcement = 900 г
        // resin_ratio=1.5 -> mass_resin = 900 * 1.5 = 1350 г
        // hardener_curve — одна точка (0.3), ratio не зависит от температуры -> mass_hardener = 1350 * 0.3 = 405 г
        // mass_gelcoat = 2 m² * 500 г/м² = 1000 г
        // mass_total = 900 + 1350 + 405 + 1000 = 3655 г
        //
        // цены: reinforcement 300 ₽/кг, resin 200 ₽/кг, hardener 400 ₽/кг, gelcoat 250 ₽/кг
        // cost_reinforcement = 0.9 кг * 300 = 270 ₽
        // cost_resin         = 1.35 кг * 200 = 270 ₽
        // cost_hardener      = 0.405 кг * 400 = 162 ₽
        // cost_gelcoat       = 1.0 кг * 250 = 250 ₽
        // cost_before_waste  = 270 + 270 + 162 + 250 = 952 ₽
        // waste_factor=0.1 -> waste_line = 95.2 ₽
        // total_cost = 952 + 95.2 = 1047.2 ₽

        $scheme = new LayupScheme([
            new LayupLayer(material: $this->reinforcement(), coverage: 1.0, overlapWaste: 0.0, position: 0),
        ]);

        $process = new Process(
            resinRatio: 1.5,
            wasteFactor: 0.1,
            hardenerCurve: [new HardenerCurvePoint(temperatureC: 20.0, ratio: 0.3)],
        );

        $prices = new PriceSnapshot([
            1 => Money::fromMajorUnits(300.0, 'RUB'),
            2 => Money::fromMajorUnits(200.0, 'RUB'),
            3 => Money::fromMajorUnits(400.0, 'RUB'),
            4 => Money::fromMajorUnits(250.0, 'RUB'),
        ]);

        $breakdown = (new LayupCalculator())->calculate(
            effectiveArea: Area::fromM2(2.0),
            scheme: $scheme,
            process: $process,
            resin: $this->resin(),
            hardener: $this->hardener(),
            prices: $prices,
            gelcoat: $this->gelcoat(),
        );

        $this->assertEqualsWithDelta(900.0, $breakdown->reinforcementLines[0]->mass->g(), 1e-9);
        $this->assertEqualsWithDelta(1350.0, $breakdown->massResin->g(), 1e-9);
        $this->assertEqualsWithDelta(405.0, $breakdown->massHardener->g(), 1e-9);
        $this->assertEqualsWithDelta(1000.0, $breakdown->massGelcoat->g(), 1e-9);
        $this->assertEqualsWithDelta(3655.0, $breakdown->massTotal->g(), 1e-9);

        $this->assertSame(27000, $breakdown->reinforcementLines[0]->cost->minorUnits());
        $this->assertSame(27000, $breakdown->costResin->minorUnits());
        $this->assertSame(16200, $breakdown->costHardener->minorUnits());
        $this->assertSame(25000, $breakdown->costGelcoat->minorUnits());
        $this->assertSame(9520, $breakdown->wasteLine->minorUnits());
        $this->assertSame(104720, $breakdown->totalCost->minorUnits());
    }

    public function test_gelcoat_is_optional(): void
    {
        $scheme = new LayupScheme([
            new LayupLayer(material: $this->reinforcement(), coverage: 1.0, overlapWaste: 0.0, position: 0),
        ]);

        $process = new Process(
            resinRatio: 1.5,
            wasteFactor: 0.1,
            hardenerCurve: [new HardenerCurvePoint(20.0, 0.3)],
        );

        $prices = new PriceSnapshot([
            1 => Money::fromMajorUnits(300.0, 'RUB'),
            2 => Money::fromMajorUnits(200.0, 'RUB'),
            3 => Money::fromMajorUnits(400.0, 'RUB'),
        ]);

        $breakdown = (new LayupCalculator())->calculate(
            effectiveArea: Area::fromM2(2.0),
            scheme: $scheme,
            process: $process,
            resin: $this->resin(),
            hardener: $this->hardener(),
            prices: $prices,
        );

        $this->assertNull($breakdown->massGelcoat);
        $this->assertNull($breakdown->costGelcoat);
    }

    public function test_uses_default_temperature_when_not_given(): void
    {
        $scheme = new LayupScheme([
            new LayupLayer(material: $this->reinforcement(), coverage: 1.0, overlapWaste: 0.0, position: 0),
        ]);

        // при 10°C ratio=0.5, при 30°C ratio=0.3 -> дефолт 20°C должен дать середину: 0.4
        $process = new Process(
            resinRatio: 1.0,
            wasteFactor: 0.0,
            hardenerCurve: [
                new HardenerCurvePoint(10.0, 0.5),
                new HardenerCurvePoint(30.0, 0.3),
            ],
        );

        $prices = new PriceSnapshot([
            1 => Money::fromMajorUnits(100.0, 'RUB'),
            2 => Money::fromMajorUnits(100.0, 'RUB'),
            3 => Money::fromMajorUnits(100.0, 'RUB'),
        ]);

        $withoutTemperature = (new LayupCalculator())->calculate(
            effectiveArea: Area::fromM2(1.0),
            scheme: $scheme,
            process: $process,
            resin: $this->resin(),
            hardener: $this->hardener(),
            prices: $prices,
        );

        $withExplicit20 = (new LayupCalculator())->calculate(
            effectiveArea: Area::fromM2(1.0),
            scheme: $scheme,
            process: $process,
            resin: $this->resin(),
            hardener: $this->hardener(),
            prices: $prices,
            temperatureC: 20.0,
        );

        $this->assertTrue($withoutTemperature->massHardener->equals($withExplicit20->massHardener));
    }

    public function test_multiple_reinforcement_layers_produce_one_line_each(): void
    {
        $scheme = new LayupScheme([
            new LayupLayer(material: new MaterialRef(1, 'Мат', 300.0), coverage: 1.0, overlapWaste: 0.0, position: 0),
            new LayupLayer(material: new MaterialRef(5, 'Ровинг', 600.0), coverage: 0.5, overlapWaste: 0.0, position: 1),
        ]);

        $process = new Process(
            resinRatio: 1.0,
            wasteFactor: 0.0,
            hardenerCurve: [new HardenerCurvePoint(20.0, 0.3)],
        );

        $prices = new PriceSnapshot([
            1 => Money::fromMajorUnits(100.0, 'RUB'),
            5 => Money::fromMajorUnits(100.0, 'RUB'),
            2 => Money::fromMajorUnits(100.0, 'RUB'),
            3 => Money::fromMajorUnits(100.0, 'RUB'),
        ]);

        $breakdown = (new LayupCalculator())->calculate(
            effectiveArea: Area::fromM2(1.0),
            scheme: $scheme,
            process: $process,
            resin: $this->resin(),
            hardener: $this->hardener(),
            prices: $prices,
        );

        $this->assertCount(2, $breakdown->reinforcementLines);
        $this->assertEqualsWithDelta(300.0, $breakdown->reinforcementLines[0]->mass->g(), 1e-9);
        $this->assertEqualsWithDelta(300.0, $breakdown->reinforcementLines[1]->mass->g(), 1e-9);
    }
}