<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioIndustry;
use App\Models\PortfolioService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioApiTest extends TestCase
{
    use RefreshDatabase;

    protected PortfolioIndustry $industry;
    protected PortfolioService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test industry
        $this->industry = PortfolioIndustry::create([
            'slug' => 'test-industry',
            'name' => 'Test Industry',
            'name_es' => 'Industria de Prueba',
            'icon' => 'TestIcon',
            'sort_order' => 1,
        ]);

        // Create test service
        $this->service = PortfolioService::create([
            'slug' => 'test-service',
            'name' => 'Test Service',
            'name_es' => 'Servicio de Prueba',
            'color' => '#3B82F6',
            'icon' => 'TestIcon',
            'sort_order' => 1,
        ]);
    }

    /** @test */
    public function it_returns_empty_list_when_no_published_portfolios(): void
    {
        $response = $this->getJson('/api/portfolio');

        $response->assertStatus(200)
            ->assertJsonPath('data', []);
    }

    /** @test */
    public function it_returns_only_published_portfolios(): void
    {
        // Create published portfolio
        $published = Portfolio::create([
            'title' => 'Published Project',
            'title_es' => 'Proyecto Publicado',
            'slug' => 'published-project',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);
        $published->services()->attach($this->service->id);

        // Create unpublished portfolio
        Portfolio::create([
            'title' => 'Draft Project',
            'slug' => 'draft-project',
            'industry_id' => $this->industry->id,
            'is_published' => false,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/portfolio');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Published Project');
    }

    /** @test */
    public function it_can_filter_featured_portfolios(): void
    {
        // Create non-featured portfolio
        $nonFeatured = Portfolio::create([
            'title' => 'Regular Project',
            'slug' => 'regular-project',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);
        $nonFeatured->services()->attach($this->service->id);

        // Create featured portfolio
        $featured = Portfolio::create([
            'title' => 'Featured Project',
            'slug' => 'featured-project',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ]);
        $featured->services()->attach($this->service->id);

        // Test filtering featured only
        $response = $this->getJson('/api/portfolio?featured=true');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Featured Project');
    }

    /** @test */
    public function it_filters_portfolios_by_service(): void
    {
        $otherService = PortfolioService::create([
            'slug' => 'other-service',
            'name' => 'Other Service',
            'name_es' => 'Otro Servicio',
            'color' => '#10B981',
            'icon' => 'OtherIcon',
            'sort_order' => 2,
        ]);

        // Portfolio with test-service
        $portfolio1 = Portfolio::create([
            'title' => 'Project A',
            'slug' => 'project-a',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $portfolio1->services()->attach($this->service->id);

        // Portfolio with other-service
        $portfolio2 = Portfolio::create([
            'title' => 'Project B',
            'slug' => 'project-b',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'sort_order' => 2,
        ]);
        $portfolio2->services()->attach($otherService->id);

        $response = $this->getJson('/api/portfolio?service=test-service');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Project A');
    }

    /** @test */
    public function it_filters_portfolios_by_industry(): void
    {
        $otherIndustry = PortfolioIndustry::create([
            'slug' => 'other-industry',
            'name' => 'Other Industry',
            'name_es' => 'Otra Industria',
            'icon' => 'OtherIcon',
            'sort_order' => 2,
        ]);

        // Portfolio with test-industry
        $portfolio1 = Portfolio::create([
            'title' => 'Project A',
            'slug' => 'project-a',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $portfolio1->services()->attach($this->service->id);

        // Portfolio with other-industry
        $portfolio2 = Portfolio::create([
            'title' => 'Project B',
            'slug' => 'project-b',
            'industry_id' => $otherIndustry->id,
            'is_published' => true,
            'sort_order' => 2,
        ]);
        $portfolio2->services()->attach($this->service->id);

        $response = $this->getJson('/api/portfolio?industry=test-industry');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Project A');
    }

    /** @test */
    public function it_shows_single_portfolio_by_slug(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Project',
            'title_es' => 'Proyecto de Prueba',
            'slug' => 'test-project',
            'industry_id' => $this->industry->id,
            'description' => 'Test description',
            'description_es' => 'Descripción de prueba',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $portfolio->services()->attach($this->service->id);

        $response = $this->getJson('/api/portfolio/test-project');

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Test Project')
            ->assertJsonPath('data.slug', 'test-project')
            ->assertJsonPath('data.description', 'Test description');
    }

    /** @test */
    public function it_returns_404_for_non_existent_portfolio(): void
    {
        $response = $this->getJson('/api/portfolio/non-existent');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_unpublished_portfolio(): void
    {
        Portfolio::create([
            'title' => 'Draft Project',
            'slug' => 'draft-project',
            'industry_id' => $this->industry->id,
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/portfolio/draft-project');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_includes_stats_in_portfolio_response(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Project',
            'slug' => 'test-project',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $portfolio->services()->attach($this->service->id);

        $portfolio->stats()->create([
            'label' => 'Traffic Increase',
            'label_es' => 'Aumento de Tráfico',
            'value' => '+245%',
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/portfolio/test-project');

        $response->assertStatus(200)
            ->assertJsonPath('data.stats.0.label', 'Traffic Increase')
            ->assertJsonPath('data.stats.0.value', '+245%');
    }

    /** @test */
    public function it_includes_features_in_portfolio_response(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Project',
            'slug' => 'test-project',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $portfolio->services()->attach($this->service->id);

        $portfolio->features()->create([
            'number' => '01',
            'title' => 'Custom Design',
            'title_es' => 'Diseño Personalizado',
            'description' => 'Fully custom design',
            'description_es' => 'Diseño completamente personalizado',
            'icon' => 'PaintBrush',
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/portfolio/test-project');

        $response->assertStatus(200)
            ->assertJsonPath('data.features.0.title', 'Custom Design')
            ->assertJsonPath('data.features.0.number', '01');
    }

    /** @test */
    public function it_includes_results_in_portfolio_response(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Project',
            'slug' => 'test-project',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $portfolio->services()->attach($this->service->id);

        $portfolio->results()->create([
            'result' => 'Increased traffic by 200%',
            'result_es' => 'Aumentó el tráfico en un 200%',
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/portfolio/test-project');

        $response->assertStatus(200)
            ->assertJsonPath('data.results.0.result', 'Increased traffic by 200%');
    }

    /** @test */
    public function it_includes_video_features_in_portfolio_response(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Project',
            'slug' => 'test-project',
            'industry_id' => $this->industry->id,
            'video_url' => 'https://youtube.com/watch?v=abc123',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        $portfolio->services()->attach($this->service->id);

        $portfolio->videoFeatures()->create([
            'title' => 'Feature 1',
            'title_es' => 'Característica 1',
            'description' => 'Description 1',
            'description_es' => 'Descripción 1',
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/portfolio/test-project');

        $response->assertStatus(200)
            ->assertJsonPath('data.videoFeatures.0.title', 'Feature 1');
    }

    /** @test */
    public function it_returns_services_list(): void
    {
        $response = $this->getJson('/api/portfolio/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    /** @test */
    public function it_returns_industries_list(): void
    {
        $response = $this->getJson('/api/portfolio/industries');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }
}
