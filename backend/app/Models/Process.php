<?php

namespace App\Models;

use Database\Factories\ProcessFactory;
use Domain\Layup\HardenerCurvePoint;
use Domain\Layup\Process as DomainProcess;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'resin_ratio', 'waste_factor', 'hardener_curve', 'notes'])]
class Process extends Model
{
    /** @use HasFactory<ProcessFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'resin_ratio' => 'float',
            'waste_factor' => 'float',
            'hardener_curve' => 'array',
        ];
    }

    public function toDomain(): DomainProcess
    {
        $points = array_map(
            fn (array $point) => new HardenerCurvePoint(
                temperatureC: (float) $point['temp_c'],
                ratio: (float) $point['ratio'],
            ),
            $this->hardener_curve,
        );

        return new DomainProcess(
            resinRatio: $this->resin_ratio,
            wasteFactor: $this->waste_factor,
            hardenerCurve: $points,
        );
    }
}
