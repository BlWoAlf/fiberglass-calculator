<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaterialResource;
use App\Models\Material;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaterialController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MaterialResource::collection(
            Material::where('is_active', true)->orderBy('name')->get(),
        );
    }
}