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
            'type' => fake()->randomElement(['nfc_plain', 'nfc_printed', 'classic']),
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
     * Indicate that the package is NFC Plain type.
     */
    public function nfcPlain(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'nfc_plain',
            'base_price' => 29.99,
        ]);
    }

    /**
     * Indicate that the package is NFC Printed type.
     */
    public function nfcPrinted(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'nfc_printed',
            'base_price' => 39.99,
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
