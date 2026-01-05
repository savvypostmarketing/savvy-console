<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_start_a_new_lead(): void
    {
        $response = $this->postJson('/api/leads/start', [
            'session_id' => 'test-session-123',
            'referrer' => 'https://google.com',
            'landing_page' => '/get-started',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'brand',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'lead' => [
                    'uuid',
                    'session_id',
                ],
            ]);

        $this->assertDatabaseHas('leads', [
            'session_id' => 'test-session-123',
            'utm_source' => 'google',
        ]);
    }

    public function test_can_update_lead_step(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->postJson('/api/leads/step', [
            'lead_uuid' => $lead->uuid,
            'step_name' => 'services',
            'step_number' => 1,
            'data' => [
                'services' => ['web-design', 'branding'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('lead_steps', [
            'lead_id' => $lead->id,
            'step_name' => 'services',
            'step_number' => 1,
        ]);
    }

    public function test_can_complete_lead(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->postJson('/api/leads/complete', [
            'lead_uuid' => $lead->uuid,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'company' => 'Acme Inc',
            'services' => ['web-design'],
            'budget' => '$10,000 - $25,000',
            'timeline' => 'Within 1 month',
            'message' => 'I need a new website',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $lead->refresh();
        $this->assertEquals('John Doe', $lead->name);
        $this->assertEquals('john@example.com', $lead->email);
        $this->assertTrue($lead->is_complete);
    }

    public function test_cannot_complete_lead_with_invalid_email(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->postJson('/api/leads/complete', [
            'lead_uuid' => $lead->uuid,
            'name' => 'John Doe',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_update_nonexistent_lead(): void
    {
        $response = $this->postJson('/api/leads/step', [
            'lead_uuid' => 'nonexistent-uuid',
            'step_name' => 'services',
            'step_number' => 1,
            'data' => [],
        ]);

        $response->assertStatus(404);
    }

    public function test_can_get_lead_status(): void
    {
        $lead = Lead::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->getJson("/api/leads/{$lead->uuid}/status");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'lead' => [
                    'uuid',
                    'is_complete',
                    'current_step',
                    'has_name',
                    'has_email',
                ],
            ]);
    }

    public function test_rate_limiting_works(): void
    {
        // Make many requests quickly
        for ($i = 0; $i < 15; $i++) {
            $this->postJson('/api/leads/start', [
                'session_id' => "session-{$i}",
            ]);
        }

        // The next request should be rate limited
        $response = $this->postJson('/api/leads/start', [
            'session_id' => 'session-rate-limited',
        ]);

        // Should get 429 Too Many Requests
        $response->assertStatus(429);
    }

    public function test_honeypot_field_marks_as_spam(): void
    {
        $response = $this->postJson('/api/leads/complete', [
            'lead_uuid' => Lead::factory()->create()->uuid,
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'website' => 'http://spam.com', // Honeypot field
        ]);

        $response->assertStatus(200);

        $lead = Lead::where('email', 'spam@example.com')->first();
        $this->assertTrue($lead->is_spam);
    }
}
