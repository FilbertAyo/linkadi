<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NfcCard>
 */
class NfcCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'profile_id' => \App\Models\Profile::factory(),
            'order_id' => \App\Models\Order::factory(),
            'package_id' => \App\Models\Package::factory(),
            'card_number' => \App\Models\NfcCard::generateCardNumber(),
            'qr_code' => null,
            'card_color' => fake()->randomElement(['black', 'white', 'silver', 'gold', 'blue']),
            'requires_printing' => fake()->boolean(30),
            'printing_text' => null,
            'design_file' => null,
            'activated_at' => null,
            'expires_at' => null,
            'status' => 'pending_production',
            'production_notes' => null,
            'tracking_number' => null,
            'shipped_at' => null,
            'delivered_at' => null,
        ];
    }

    /**
     * Indicate that the card is activated.
     */
    public function activated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'activated',
            'activated_at' => now()->subDays(rand(1, 30)),
            'expires_at' => now()->addYear(),
        ]);
    }

    /**
     * Indicate that the card is in production.
     */
    public function inProduction(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_production',
        ]);
    }

    /**
     * Indicate that the card is shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'tracking_number' => 'TRK' . fake()->numerify('##########'),
            'shipped_at' => now()->subDays(rand(1, 5)),
        ]);
    }

    /**
     * Indicate that the card requires printing.
     */
    public function withPrinting(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_printing' => true,
            'printing_text' => [
                'name' => fake()->name(),
                'title' => fake()->jobTitle(),
            ],
        ]);
    }
}
