<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'name' => $this->name,
            'areal_density_gsm' => $this->areal_density_gsm,
            'bulk_density' => $this->bulk_density,
            'unit' => $this->unit->value,
            'vendor' => $this->vendor,
            'is_active' => $this->is_active,
        ];
    }
}