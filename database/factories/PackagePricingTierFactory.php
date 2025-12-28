<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackagePricingTier>
 */
class PackagePricingTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => fake()->randomFloat(2, 1, 5),
            'total_price' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the tier has no maximum quantity (unlimited).
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_quantity' => null,
        ]);
    }

    /**
     * Indicate that the tier is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
