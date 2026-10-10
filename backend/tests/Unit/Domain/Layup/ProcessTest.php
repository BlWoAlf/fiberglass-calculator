<?php

namespace Tests\Unit\Domain\Layup;

use Domain\Layup\HardenerCurvePoint;
use Domain\Layup\Process;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    private function process(): Process
    {
        return new Process(
            resinRatio: 1.5,
            wasteFactor: 0.1,
            hardenerCurve: [
                new HardenerCurvePoint(temperatureC: 15.0, ratio: 0.40),
                new HardenerCurvePoint(temperatureC: 25.0, ratio: 0.30),
            ],
        );
    }

    public function test_creates_with_valid_data(): void
    {
        $process = $this->process();

        $this->assertSame(1.5, $process->resinRatio);
        $this->assertSame(0.1, $process->wasteFactor);
    }

    public function test_rejects_negative_resin_ratio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Process(
            resinRatio: -1.0,
            wasteFactor: 0.1,
            hardenerCurve: [new HardenerCurvePoint(20.0, 0.3)],
        );
    }

    public function test_rejects_negative_waste_factor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Process(
            resinRatio: 1.5,
            wasteFactor: -0.1,
            hardenerCurve: [new HardenerCurvePoint(20.0, 0.3)],
        );
    }

    public function test_rejects_empty_hardener_curve(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Process(resinRatio: 1.5, wasteFactor: 0.1, hardenerCurve: []);
    }

    public function test_rejects_non_hardener_curve_point_entries(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Process(resinRatio: 1.5, wasteFactor: 0.1, hardenerCurve: [['temp_c' => 20.0, 'ratio' => 0.3]]);
    }

    public function test_rejects_duplicate_temperature_points(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Process(
            resinRatio: 1.5,
            wasteFactor: 0.1,
            hardenerCurve: [
                new HardenerCurvePoint(20.0, 0.3),
                new HardenerCurvePoint(20.0, 0.35),
            ],
        );
    }

    public function test_hardener_ratio_at_exact_point(): void
    {
        $process = $this->process();

        $this->assertEqualsWithDelta(0.40, $process->hardenerRatio(15.0), 1e-9);
        $this->assertEqualsWithDelta(0.30, $process->hardenerRatio(25.0), 1e-9);
    }

    public function test_hardener_ratio_interpolates_between_points(): void
    {
        $process = $this->process();

        // середина отрезка 15..25 -> середина отрезка 0.40..0.30
        $this->assertEqualsWithDelta(0.35, $process->hardenerRatio(20.0), 1e-9);
    }

    public function test_hardener_ratio_interpolates_at_quarter_point(): void
    {
        $process = $this->process();

        // 17.5 — четверть пути от 15 до 25 -> четверть пути от 0.40 до 0.30
        $this->assertEqualsWithDelta(0.375, $process->hardenerRatio(17.5), 1e-9);
    }

    public function test_hardener_ratio_clamps_below_lowest_point(): void
    {
        $process = $this->process();

        $this->assertEqualsWithDelta(0.40, $process->hardenerRatio(-10.0), 1e-9);
    }

    public function test_hardener_ratio_clamps_above_highest_point(): void
    {
        $process = $this->process();

        $this->assertEqualsWithDelta(0.30, $process->hardenerRatio(40.0), 1e-9);
    }

    public function test_hardener_ratio_works_regardless_of_input_point_order(): void
    {
        // точки переданы в обратном порядке — Process должен сам отсортировать
        $process = new Process(
            resinRatio: 1.5,
            wasteFactor: 0.1,
            hardenerCurve: [
                new HardenerCurvePoint(25.0, 0.30),
                new HardenerCurvePoint(15.0, 0.40),
            ],
        );

        $this->assertEqualsWithDelta(0.35, $process->hardenerRatio(20.0), 1e-9);
    }

    public function test_hardener_ratio_constant_for_single_point_curve(): void
    {
        $process = new Process(
            resinRatio: 1.5,
            wasteFactor: 0.1,
            hardenerCurve: [new HardenerCurvePoint(20.0, 0.33)],
        );

        $this->assertEqualsWithDelta(0.33, $process->hardenerRatio(-50.0), 1e-9);
        $this->assertEqualsWithDelta(0.33, $process->hardenerRatio(20.0), 1e-9);
        $this->assertEqualsWithDelta(0.33, $process->hardenerRatio(100.0), 1e-9);
    }

    public function test_hardener_ratio_rejects_non_finite_temperature(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->process()->hardenerRatio(NAN);
    }
}