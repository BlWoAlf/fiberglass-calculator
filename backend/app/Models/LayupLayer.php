<?php

namespace App\Models;

use Database\Factories\LayupLayerFactory;
use Domain\Layup\LayupLayer as DomainLayupLayer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['scheme_id', 'position', 'material_id', 'coverage', 'overlap_waste', 'note'])]
class LayupLayer extends Model
{
    /** @use HasFactory<LayupLayerFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'coverage' => 'float',
            'overlap_waste' => 'float',
        ];
    }

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(LayupScheme::class, 'scheme_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function toDomain(): DomainLayupLayer
    {
        return new DomainLayupLayer(
            material: $this->material->toMaterialRef(),
            coverage: $this->coverage,
            overlapWaste: $this->overlap_waste,
            position: $this->position,
        );
    }
}