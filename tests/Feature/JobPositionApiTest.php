<?php

namespace Tests\Feature;

use App\Models\JobPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPositionApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_empty_list_when_no_active_positions(): void
    {
        $response = $this->getJson('/api/job-positions');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', []);
    }

    /** @test */
    public function it_returns_only_active_positions(): void
    {
        // Create active position
        JobPosition::create([
            'title' => 'Active Position',
            'title_es' => 'Posición Activa',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create inactive position
        JobPosition::create([
            'title' => 'Inactive Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/job-positions');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Active Position');
    }

    /** @test */
    public function it_returns_positions_ordered_by_sort_order(): void
    {
        JobPosition::create([
            'title' => 'Third Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        JobPosition::create([
            'title' => 'First Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        JobPosition::create([
            'title' => 'Second Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/job-positions');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'First Position')
            ->assertJsonPath('data.1.title', 'Second Position')
            ->assertJsonPath('data.2.title', 'Third Position');
    }

    /** @test */
    public function it_returns_localized_labels_in_english(): void
    {
        JobPosition::create([
            'title' => 'Developer',
            'title_es' => 'Desarrollador',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/job-positions?locale=en');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Developer')
            ->assertJsonPath('data.0.employment_type_label', 'Full Time')
            ->assertJsonPath('data.0.location_type_label', 'Remote');
    }

    /** @test */
    public function it_returns_localized_labels_in_spanish(): void
    {
        JobPosition::create([
            'title' => 'Developer',
            'title_es' => 'Desarrollador',
            'employment_type' => 'full-time',
            'location_type' => 'hybrid',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/job-positions?locale=es');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Desarrollador')
            ->assertJsonPath('data.0.employment_type_label', 'Tiempo Completo')
            ->assertJsonPath('data.0.location_type_label', 'Híbrido');
    }

    /** @test */
    public function it_returns_all_employment_types_correctly(): void
    {
        $types = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'internship' => 'Internship',
        ];

        foreach ($types as $type => $label) {
            JobPosition::create([
                'title' => "Position {$type}",
                'employment_type' => $type,
                'location_type' => 'remote',
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $response = $this->getJson('/api/job-positions?locale=en');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function it_returns_all_location_types_correctly(): void
    {
        $types = [
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'on-site' => 'On-site',
        ];

        foreach ($types as $type => $label) {
            JobPosition::create([
                'title' => "Position {$type}",
                'employment_type' => 'full-time',
                'location_type' => $type,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $response = $this->getJson('/api/job-positions?locale=en');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_includes_apply_url_from_linkedin(): void
    {
        JobPosition::create([
            'title' => 'Developer',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'linkedin_url' => 'https://linkedin.com/jobs/123',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/job-positions');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.apply_url', 'https://linkedin.com/jobs/123');
    }

    /** @test */
    public function it_uses_apply_url_when_linkedin_not_set(): void
    {
        JobPosition::create([
            'title' => 'Developer',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'apply_url' => 'https://example.com/apply',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/job-positions');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.apply_url', 'https://example.com/apply');
    }

    /** @test */
    public function it_prefers_linkedin_url_over_apply_url(): void
    {
        JobPosition::create([
            'title' => 'Developer',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'linkedin_url' => 'https://linkedin.com/jobs/123',
            'apply_url' => 'https://example.com/apply',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/job-positions');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.apply_url', 'https://linkedin.com/jobs/123');
    }

    /** @test */
    public function it_includes_department_and_location(): void
    {
        JobPosition::create([
            'title' => 'Developer',
            'department' => 'Engineering',
            'employment_type' => 'full-time',
            'location_type' => 'hybrid',
            'location' => 'New York, USA',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/job-positions');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.department', 'Engineering')
            ->assertJsonPath('data.0.location', 'New York, USA');
    }

    /** @test */
    public function it_includes_description(): void
    {
        JobPosition::create([
            'title' => 'Developer',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'description' => 'We are looking for a developer...',
            'description_es' => 'Buscamos un desarrollador...',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/job-positions?locale=en');
        $response->assertJsonPath('data.0.description', 'We are looking for a developer...');

        $response = $this->getJson('/api/job-positions?locale=es');
        $response->assertJsonPath('data.0.description', 'Buscamos un desarrollador...');
    }

    /** @test */
    public function it_includes_featured_flag(): void
    {
        JobPosition::create([
            'title' => 'Featured Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        JobPosition::create([
            'title' => 'Regular Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/job-positions');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.is_featured', true)
            ->assertJsonPath('data.1.is_featured', false);
    }
}
