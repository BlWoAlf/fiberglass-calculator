<?php

namespace App\Models;

use App\Enums\MaterialType;
use App\Enums\MaterialUnit;
use Database\Factories\MaterialFactory;
use DateTimeInterface;
use Domain\Layup\MaterialRef;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['type', 'name', 'areal_density_gsm', 'bulk_density', 'unit', 'vendor', 'is_active'])]
class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => MaterialType::class,
            'unit' => MaterialUnit::class,
            'areal_density_gsm' => 'float',
            'bulk_density' => 'float',
            'is_active' => 'bool',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(MaterialPrice::class);
    }

    public function currentPriceAt(DateTimeInterface $at): ?MaterialPrice
    {
        return $this->prices()
            ->where('valid_from', '<=', $at->format('Y-m-d H:i:s'))
            ->orderByDesc('valid_from')
            ->first();
    }

    public function toMaterialRef(): MaterialRef
    {
        return new MaterialRef(
            id: $this->id,
            name: $this->name,
            arealDensityGsm: $this->areal_density_gsm ?? 0.0,
        );
    }
}