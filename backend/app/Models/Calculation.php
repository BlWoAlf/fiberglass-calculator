<?php

namespace App\Models;

use App\Enums\InputMode;
use Database\Factories\CalculationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'input_mode', 'scheme_id', 'process_id', 'quick_params',
    'effective_area_mm2', 'results', 'mass_total_g', 'total_cost_minor',
    'currency', 'prices_snapshot', 'temperature_c',
])]
class Calculation extends Model
{
    /** @use HasFactory<CalculationFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'input_mode' => InputMode::class,
            'quick_params' => 'array',
            'effective_area_mm2' => 'float',
            'results' => 'array',
            'mass_total_g' => 'float',
            'total_cost_minor' => 'integer',
            'prices_snapshot' => 'array',
            'temperature_c' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(LayupScheme::class, 'scheme_id');
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }
}