<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SefEfakturaSetting>
 */
class SefEfakturaSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'api_key' => fake()->sha256(),
            'is_enabled' => true,
            'default_vat_exemption' => fake()->randomElement([
                'PDV-RS-33',
                'PDV-RS-35-7',
                'PDV-RS-36-5',
                'PDV-RS-36b-4',
            ]),
            'default_vat_category' => 'SS',
            'webhook_url' => fake()->url(),
            'last_webhook_test' => now(),
            'integration_data' => null,
        ];
    }

    /**
     * Indicate that the SEF integration is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    /**
     * Indicate that the API key is missing.
     */
    public function withoutApiKey(): static
    {
        return $this->state(fn (array $attributes) => [
            'api_key' => null,
        ]);
    }

    /**
     * Create settings for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
