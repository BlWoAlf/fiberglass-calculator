<?php

namespace Database\Seeders;

use App\Enums\MaterialType;
use App\Enums\MaterialUnit;
use App\Models\Material;
use App\Models\MaterialPrice;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Базовый справочник материалов с текущей ценой (RUB/кг).
     *
     * Цены и плотности — ориентировочные рыночные значения, не калибровка
     * под конкретного поставщика. Правятся через UI/БД по факту закупок.
     */
    public function run(): void
    {
        $materials = [
            ['type' => MaterialType::Mat, 'name' => 'Стекломат 300', 'areal_density_gsm' => 300.0, 'bulk_density' => null, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Ahlstrom', 'price_minor' => 28_000],
            ['type' => MaterialType::Mat, 'name' => 'Стекломат 450', 'areal_density_gsm' => 450.0, 'bulk_density' => null, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Ahlstrom', 'price_minor' => 26_000],
            ['type' => MaterialType::Roving, 'name' => 'Стеклоровинг 600', 'areal_density_gsm' => 600.0, 'bulk_density' => null, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Interglas', 'price_minor' => 24_000],
            ['type' => MaterialType::Twill, 'name' => 'Твил 390', 'areal_density_gsm' => 390.0, 'bulk_density' => null, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Interglas', 'price_minor' => 45_000],
            ['type' => MaterialType::Veil, 'name' => 'Поверхностная вуаль 30', 'areal_density_gsm' => 30.0, 'bulk_density' => null, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Interglas', 'price_minor' => 60_000],
            ['type' => MaterialType::Resin, 'name' => 'Полиэфирная смола', 'areal_density_gsm' => null, 'bulk_density' => 1150.0, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Ashland', 'price_minor' => 18_000],
            ['type' => MaterialType::Hardener, 'name' => 'Отвердитель МЭКП', 'areal_density_gsm' => null, 'bulk_density' => 1100.0, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Akzo Nobel', 'price_minor' => 35_000],
            ['type' => MaterialType::Gelcoat, 'name' => 'Гелькоут', 'areal_density_gsm' => 500.0, 'bulk_density' => null, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Scott Bader', 'price_minor' => 40_000],
            ['type' => MaterialType::Filler, 'name' => 'Аэросил', 'areal_density_gsm' => null, 'bulk_density' => 100.0, 'unit' => MaterialUnit::Kilogram, 'vendor' => 'Evonik', 'price_minor' => 50_000],
            ['type' => MaterialType::Core, 'name' => 'ПВХ-пена 80', 'areal_density_gsm' => null, 'bulk_density' => 80.0, 'unit' => MaterialUnit::SquareMeter, 'vendor' => 'Diab', 'price_minor' => 120_000],
        ];

        foreach ($materials as $data) {
            $material = Material::create([
                'type' => $data['type'],
                'name' => $data['name'],
                'areal_density_gsm' => $data['areal_density_gsm'],
                'bulk_density' => $data['bulk_density'],
                'unit' => $data['unit'],
                'vendor' => $data['vendor'],
                'is_active' => true,
            ]);

            MaterialPrice::create([
                'material_id' => $material->id,
                'price_minor' => $data['price_minor'],
                'currency' => 'RUB',
                'per_unit' => MaterialUnit::Kilogram,
                'vendor_sku' => null,
                'valid_from' => now()->subMonth(),
            ]);
        }
    }
}