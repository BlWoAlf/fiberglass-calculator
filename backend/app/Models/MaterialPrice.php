<?php

namespace App\Models;

use App\Enums\MaterialUnit;
use Database\Factories\MaterialPriceFactory;
use Domain\ValueObject\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['material_id', 'price_minor', 'currency', 'per_unit', 'vendor_sku', 'valid_from'])]
class MaterialPrice extends Model
{
    /** @use HasFactory<MaterialPriceFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'per_unit' => MaterialUnit::class,
            'valid_from' => 'datetime',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function toMoney(): Money
    {
        return Money::fromMinorUnits($this->price_minor, $this->currency);
    }
}