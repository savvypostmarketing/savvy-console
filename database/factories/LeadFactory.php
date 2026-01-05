<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'session_id' => Str::random(32),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->company(),
            'services' => fake()->randomElements(
                ['web-design', 'branding', 'digital-marketing', 'development', 'consulting'],
                fake()->numberBetween(1, 3)
            ),
            'budget' => fake()->randomElement(['Under $5,000', '$5,000 - $10,000', '$10,000 - $25,000', 'Over $25,000']),
            'timeline' => fake()->randomElement(['Immediately', 'Within 1 month', 'Within 3 months', 'No rush']),
            'message' => fake()->paragraph(),
            'status' => 'new',
            'spam_score' => fake()->numberBetween(0, 30),
            'is_spam' => false,
            'is_complete' => true,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referrer' => fake()->url(),
            'landing_page' => '/get-started',
            'utm_source' => fake()->randomElement(['google', 'facebook', 'linkedin', null]),
            'utm_medium' => fake()->randomElement(['cpc', 'organic', 'social', null]),
            'utm_campaign' => fake()->randomElement(['brand', 'leads', 'awareness', null]),
        ];
    }

    /**
     * Indicate that the lead is incomplete.
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => null,
            'email' => null,
            'is_complete' => false,
        ]);
    }

    /**
     * Indicate that the lead is spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_spam' => true,
            'spam_score' => fake()->numberBetween(70, 100),
        ]);
    }

    /**
     * Indicate that the lead has a specific status.
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
