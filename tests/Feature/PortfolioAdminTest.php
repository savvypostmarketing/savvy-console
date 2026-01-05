<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Portfolio;
use App\Models\PortfolioIndustry;
use App\Models\PortfolioService;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortfolioAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PortfolioIndustry $industry;
    protected PortfolioService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with admin role
        $this->user = User::factory()->create();

        // Create super admin role (bypasses all permission checks)
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'level' => 100,
            'is_system' => true,
        ]);

        $this->user->assignRole($superAdminRole);

        // Create test industry
        $this->industry = PortfolioIndustry::create([
            'slug' => 'test-industry',
            'name' => 'Test Industry',
            'name_es' => 'Industria de Prueba',
            'icon' => 'TestIcon',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create test service
        $this->service = PortfolioService::create([
            'slug' => 'test-service',
            'name' => 'Test Service',
            'name_es' => 'Servicio de Prueba',
            'color' => '#3B82F6',
            'icon' => 'TestIcon',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    /** @test */
    public function guests_cannot_access_portfolio_admin(): void
    {
        $response = $this->get('/admin/portfolio');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_users_can_access_portfolio_index(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/portfolio');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_users_can_access_create_page(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/portfolio/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_users_can_access_edit_page(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Project',
            'slug' => 'test-project',
            'industry_id' => $this->industry->id,
            'sort_order' => 1,
        ]);
        $portfolio->services()->attach($this->service->id);

        $response = $this->actingAs($this->user)->get("/admin/portfolio/{$portfolio->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function portfolio_model_uses_soft_deletes(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'To Delete',
            'slug' => 'to-delete',
            'industry_id' => $this->industry->id,
            'sort_order' => 1,
        ]);

        // Manually test soft delete behavior at model level
        $portfolio->delete();

        // Should be soft deleted (still in DB but with deleted_at set)
        $this->assertSoftDeleted('portfolios', [
            'id' => $portfolio->id,
        ]);

        // Should not appear in regular queries
        $this->assertNull(Portfolio::find($portfolio->id));

        // Should appear in withTrashed queries
        $this->assertNotNull(Portfolio::withTrashed()->find($portfolio->id));
    }

    /** @test */
    public function portfolio_model_auto_generates_uuid(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test UUID',
            'slug' => 'test-uuid',
            'industry_id' => $this->industry->id,
            'sort_order' => 1,
        ]);

        $this->assertNotNull($portfolio->uuid);
        $this->assertTrue(Str::isUuid($portfolio->uuid));
    }

    /** @test */
    public function portfolio_can_have_stats(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Stats',
            'slug' => 'test-stats',
            'industry_id' => $this->industry->id,
            'sort_order' => 1,
        ]);

        $portfolio->stats()->create([
            'label' => 'Traffic',
            'label_es' => 'Tráfico',
            'value' => '+200%',
            'sort_order' => 0,
        ]);

        $this->assertCount(1, $portfolio->stats);
        $this->assertEquals('Traffic', $portfolio->stats->first()->label);
    }

    /** @test */
    public function portfolio_can_have_features(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Features',
            'slug' => 'test-features',
            'industry_id' => $this->industry->id,
            'sort_order' => 1,
        ]);

        $portfolio->features()->create([
            'number' => '01',
            'title' => 'Custom Design',
            'title_es' => 'Diseño Personalizado',
            'description' => 'Fully custom design',
            'icon' => 'PaintBrush',
            'sort_order' => 0,
        ]);

        $this->assertCount(1, $portfolio->features);
        $this->assertEquals('Custom Design', $portfolio->features->first()->title);
    }

    /** @test */
    public function portfolio_can_have_results(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Results',
            'slug' => 'test-results',
            'industry_id' => $this->industry->id,
            'sort_order' => 1,
        ]);

        $portfolio->results()->create([
            'result' => 'Increased traffic by 200%',
            'result_es' => 'Aumentó el tráfico en un 200%',
            'sort_order' => 0,
        ]);

        $this->assertCount(1, $portfolio->results);
        $this->assertEquals('Increased traffic by 200%', $portfolio->results->first()->result);
    }

    /** @test */
    public function portfolio_can_have_video_features(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Video',
            'slug' => 'test-video',
            'industry_id' => $this->industry->id,
            'video_url' => 'https://youtube.com/watch?v=abc123',
            'sort_order' => 1,
        ]);

        $portfolio->videoFeatures()->create([
            'title' => 'Feature 1',
            'title_es' => 'Característica 1',
            'description' => 'Description 1',
            'sort_order' => 0,
        ]);

        $this->assertCount(1, $portfolio->videoFeatures);
        $this->assertEquals('Feature 1', $portfolio->videoFeatures->first()->title);
    }

    /** @test */
    public function portfolio_belongs_to_industry(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Industry Relation',
            'slug' => 'test-industry-relation',
            'industry_id' => $this->industry->id,
            'sort_order' => 1,
        ]);

        $this->assertEquals($this->industry->id, $portfolio->industry->id);
        $this->assertEquals('Test Industry', $portfolio->industry->name);
    }

    /** @test */
    public function portfolio_has_many_services(): void
    {
        $portfolio = Portfolio::create([
            'title' => 'Test Services Relation',
            'slug' => 'test-services-relation',
            'industry_id' => $this->industry->id,
            'sort_order' => 1,
        ]);

        $portfolio->services()->attach($this->service->id);

        $this->assertCount(1, $portfolio->services);
        $this->assertEquals($this->service->id, $portfolio->services->first()->id);
    }

    /** @test */
    public function published_scope_filters_portfolios(): void
    {
        Portfolio::create([
            'title' => 'Published',
            'slug' => 'published',
            'industry_id' => $this->industry->id,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        Portfolio::create([
            'title' => 'Draft',
            'slug' => 'draft',
            'industry_id' => $this->industry->id,
            'is_published' => false,
            'sort_order' => 2,
        ]);

        $published = Portfolio::published()->get();

        $this->assertCount(1, $published);
        $this->assertEquals('Published', $published->first()->title);
    }

    /** @test */
    public function featured_scope_filters_portfolios(): void
    {
        Portfolio::create([
            'title' => 'Featured',
            'slug' => 'featured',
            'industry_id' => $this->industry->id,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        Portfolio::create([
            'title' => 'Regular',
            'slug' => 'regular',
            'industry_id' => $this->industry->id,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $featured = Portfolio::featured()->get();

        $this->assertCount(1, $featured);
        $this->assertEquals('Featured', $featured->first()->title);
    }
}
