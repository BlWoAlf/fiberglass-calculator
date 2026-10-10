<?php

namespace Tests\Unit\Domain\Layup;

use Domain\Layup\HardenerCurvePoint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HardenerCurvePointTest extends TestCase
{
    public function test_creates_with_valid_data(): void
    {
        $point = new HardenerCurvePoint(temperatureC: 20.0, ratio: 0.33);

        $this->assertSame(20.0, $point->temperatureC);
        $this->assertSame(0.33, $point->ratio);
    }

    public function test_rejects_negative_ratio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HardenerCurvePoint(temperatureC: 20.0, ratio: -0.1);
    }

    public function test_rejects_non_finite_ratio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HardenerCurvePoint(temperatureC: 20.0, ratio: NAN);
    }

    public function test_rejects_non_finite_temperature(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HardenerCurvePoint(temperatureC: INF, ratio: 0.33);
    }

    public function test_allows_negative_temperature(): void
    {
        // цех без отопления зимой — валидный сценарий
        $point = new HardenerCurvePoint(temperatureC: -10.0, ratio: 0.5);

        $this->assertSame(-10.0, $point->temperatureC);
    }
}