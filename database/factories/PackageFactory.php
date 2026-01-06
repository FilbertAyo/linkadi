<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['nfc_card', 'classic']),
            'image' => null,
            'is_active' => true,
            'display_order' => 0,
            'features' => [
                'Feature 1',
                'Feature 2',
                'Feature 3',
            ],
            'base_price' => fake()->randomFloat(2, 10, 100),
        ];
    }

    /**
     * Indicate that the package is NFC Card type.
     */
    public function nfcCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'nfc_card',
            'base_price' => 30000.00,
        ]);
    }

    /**
     * Indicate that the package is Classic type.
     */
    public function classic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'classic',
            'base_price' => null,
        ]);
    }

    /**
     * Indicate that the package is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
