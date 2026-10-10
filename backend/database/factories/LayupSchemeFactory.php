<?php

namespace Database\Factories;

use App\Models\LayupScheme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LayupScheme>
 */
class LayupSchemeFactory extends Factory
{
    protected $model = LayupScheme::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_template' => false,
        ];
    }

    public function template(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_template' => true,
        ]);
    }
}