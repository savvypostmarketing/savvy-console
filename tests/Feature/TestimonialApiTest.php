<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_empty_list_when_no_published_testimonials(): void
    {
        $response = $this->getJson('/api/testimonials');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', []);
    }

    /** @test */
    public function it_returns_only_published_testimonials(): void
    {
        // Create published testimonial
        Testimonial::create([
            'name' => 'John Doe',
            'quote' => 'Great service!',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        // Create unpublished testimonial
        Testimonial::create([
            'name' => 'Jane Doe',
            'quote' => 'Draft testimonial',
            'rating' => 5,
            'source' => 'website',
            'is_published' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/testimonials');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'John Doe');
    }

    /** @test */
    public function it_returns_testimonials_ordered_by_sort_order(): void
    {
        Testimonial::create([
            'name' => 'Third',
            'quote' => 'Quote 3',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'sort_order' => 3,
        ]);

        Testimonial::create([
            'name' => 'First',
            'quote' => 'Quote 1',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Second',
            'quote' => 'Quote 2',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/testimonials');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'First')
            ->assertJsonPath('data.1.name', 'Second')
            ->assertJsonPath('data.2.name', 'Third');
    }

    /** @test */
    public function it_can_filter_featured_testimonials(): void
    {
        Testimonial::create([
            'name' => 'Featured User',
            'quote' => 'Amazing!',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Regular User',
            'quote' => 'Good!',
            'rating' => 4,
            'source' => 'website',
            'is_published' => true,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/testimonials?featured=true');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Featured User');
    }

    /** @test */
    public function it_can_filter_by_service(): void
    {
        Testimonial::create([
            'name' => 'Web User',
            'quote' => 'Great website!',
            'rating' => 5,
            'source' => 'website',
            'services' => ['website', 'seo'],
            'is_published' => true,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Branding User',
            'quote' => 'Great branding!',
            'rating' => 5,
            'source' => 'website',
            'services' => ['branding'],
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/testimonials?service=website');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Web User');
    }

    /** @test */
    public function it_respects_limit_parameter(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Testimonial::create([
                'name' => "User {$i}",
                'quote' => "Quote {$i}",
                'rating' => 5,
                'source' => 'website',
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }

        $response = $this->getJson('/api/testimonials?limit=3');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_returns_localized_content_in_english(): void
    {
        Testimonial::create([
            'name' => 'John Doe',
            'role' => 'CEO',
            'role_es' => 'Director Ejecutivo',
            'company' => 'Acme Inc',
            'company_es' => 'Acme Inc',
            'quote' => 'Great service!',
            'quote_es' => 'Excelente servicio!',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/testimonials?locale=en');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.role', 'CEO')
            ->assertJsonPath('data.0.quote', 'Great service!');
    }

    /** @test */
    public function it_returns_localized_content_in_spanish(): void
    {
        Testimonial::create([
            'name' => 'John Doe',
            'role' => 'CEO',
            'role_es' => 'Director Ejecutivo',
            'company' => 'Acme Inc',
            'company_es' => 'Acme Inc',
            'quote' => 'Great service!',
            'quote_es' => 'Excelente servicio!',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/testimonials?locale=es');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.role', 'Director Ejecutivo')
            ->assertJsonPath('data.0.quote', 'Excelente servicio!');
    }

    /** @test */
    public function it_returns_featured_testimonials(): void
    {
        Testimonial::create([
            'name' => 'Featured User',
            'quote' => 'Amazing!',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Regular User',
            'quote' => 'Good!',
            'rating' => 4,
            'source' => 'website',
            'is_published' => true,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/testimonials/featured');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Featured User');
    }

    /** @test */
    public function it_returns_testimonials_by_service(): void
    {
        Testimonial::create([
            'name' => 'Web User',
            'quote' => 'Great website!',
            'rating' => 5,
            'source' => 'website',
            'services' => ['website', 'seo'],
            'is_published' => true,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Branding User',
            'quote' => 'Great branding!',
            'rating' => 5,
            'source' => 'website',
            'services' => ['branding'],
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/testimonials/service/website');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Web User')
            ->assertJsonPath('service', 'website');
    }

    /** @test */
    public function it_includes_project_info_when_available(): void
    {
        Testimonial::create([
            'name' => 'John Doe',
            'quote' => 'Great!',
            'rating' => 5,
            'source' => 'portfolio',
            'project_title' => 'Website Redesign',
            'project_title_es' => 'Rediseño de Sitio Web',
            'project_screenshot' => '/images/project.jpg',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/testimonials?locale=en');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.project_title', 'Website Redesign')
            ->assertJsonPath('data.0.project_screenshot', '/images/project.jpg');
    }

    /** @test */
    public function it_includes_extra_info_and_date_label(): void
    {
        Testimonial::create([
            'name' => 'John Doe',
            'quote' => 'Great!',
            'rating' => 5,
            'source' => 'google',
            'date_label' => 'a year ago',
            'extra_info' => 'Local Guide · 14 reviews',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/testimonials');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.date_label', 'a year ago')
            ->assertJsonPath('data.0.extra_info', 'Local Guide · 14 reviews');
    }

    /** @test */
    public function it_includes_services_array(): void
    {
        Testimonial::create([
            'name' => 'John Doe',
            'quote' => 'Great!',
            'rating' => 5,
            'source' => 'website',
            'services' => ['website', 'branding', 'seo'],
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/testimonials');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.services', ['website', 'branding', 'seo']);
    }

    /** @test */
    public function it_includes_total_count(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Testimonial::create([
                'name' => "User {$i}",
                'quote' => "Quote {$i}",
                'rating' => 5,
                'source' => 'website',
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }

        $response = $this->getJson('/api/testimonials?limit=3');

        $response->assertStatus(200)
            ->assertJsonPath('total', 3); // Total in response matches limit applied
    }
}
