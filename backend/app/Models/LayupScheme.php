<?php

namespace App\Models;

use Database\Factories\LayupSchemeFactory;
use Domain\Layup\LayupScheme as DomainLayupScheme;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'description', 'is_template'])]
class LayupScheme extends Model
{
    /** @use HasFactory<LayupSchemeFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'is_template' => 'bool',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function layers(): HasMany
    {
        return $this->hasMany(LayupLayer::class, 'scheme_id')->orderBy('position');
    }

    public function toDomain(): DomainLayupScheme
    {
        return new DomainLayupScheme(
            $this->layers->map(fn (LayupLayer $layer) => $layer->toDomain())->all(),
        );
    }
}