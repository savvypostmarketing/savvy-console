<?php

namespace Tests\Feature;

use App\Models\JobPosition;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPositionAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create super admin role
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'level' => 100,
            'is_system' => true,
        ]);

        // Create regular admin role
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin access',
            'level' => 50,
            'is_system' => false,
        ]);

        // Create manage-settings permission
        $manageSettingsPermission = Permission::create([
            'name' => 'Manage Settings',
            'slug' => 'manage-settings',
            'description' => 'Can manage settings',
            'group' => 'settings',
        ]);

        // Create super admin user
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superAdminRole);

        // Create regular user without manage-settings permission
        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole($adminRole);
    }

    /** @test */
    public function guests_cannot_access_job_positions_admin(): void
    {
        $response = $this->get('/admin/job-positions');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function users_without_permission_cannot_access_job_positions(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/job-positions');

        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_access_job_positions_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/job-positions');

        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_can_access_create_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/job-positions/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_can_create_job_position(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/job-positions', [
            'title' => 'Software Developer',
            'title_es' => 'Desarrollador de Software',
            'department' => 'Engineering',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'location' => 'USA',
            'description' => 'Looking for a developer',
            'description_es' => 'Buscamos desarrollador',
            'linkedin_url' => 'https://linkedin.com/jobs/123',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $response->assertRedirect('/admin/job-positions');

        $this->assertDatabaseHas('job_positions', [
            'title' => 'Software Developer',
            'title_es' => 'Desarrollador de Software',
            'department' => 'Engineering',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
        ]);
    }

    /** @test */
    public function super_admin_can_access_edit_page(): void
    {
        $position = JobPosition::create([
            'title' => 'Test Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/admin/job-positions/{$position->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_can_update_job_position(): void
    {
        $position = JobPosition::create([
            'title' => 'Original Title',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->superAdmin)->put("/admin/job-positions/{$position->id}", [
            'title' => 'Updated Title',
            'title_es' => 'Título Actualizado',
            'employment_type' => 'part-time',
            'location_type' => 'hybrid',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ]);

        $response->assertRedirect('/admin/job-positions');

        $this->assertDatabaseHas('job_positions', [
            'id' => $position->id,
            'title' => 'Updated Title',
            'employment_type' => 'part-time',
            'location_type' => 'hybrid',
            'is_featured' => true,
        ]);
    }

    /** @test */
    public function super_admin_can_delete_job_position(): void
    {
        $position = JobPosition::create([
            'title' => 'To Delete',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->superAdmin)->delete("/admin/job-positions/{$position->id}");

        $response->assertRedirect('/admin/job-positions');

        $this->assertDatabaseMissing('job_positions', [
            'id' => $position->id,
        ]);
    }

    /** @test */
    public function super_admin_can_toggle_active_status(): void
    {
        $position = JobPosition::create([
            'title' => 'Test Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->superAdmin)->patch("/admin/job-positions/{$position->id}/toggle-active");

        $response->assertRedirect();

        $this->assertDatabaseHas('job_positions', [
            'id' => $position->id,
            'is_active' => false,
        ]);

        // Toggle back
        $this->actingAs($this->superAdmin)->patch("/admin/job-positions/{$position->id}/toggle-active");

        $this->assertDatabaseHas('job_positions', [
            'id' => $position->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function super_admin_can_toggle_featured_status(): void
    {
        $position = JobPosition::create([
            'title' => 'Test Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->superAdmin)->patch("/admin/job-positions/{$position->id}/toggle-featured");

        $response->assertRedirect();

        $this->assertDatabaseHas('job_positions', [
            'id' => $position->id,
            'is_featured' => true,
        ]);
    }

    /** @test */
    public function create_requires_title(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/job-positions', [
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'sort_order' => 1,
        ]);

        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function create_requires_valid_employment_type(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/job-positions', [
            'title' => 'Test Position',
            'employment_type' => 'invalid-type',
            'location_type' => 'remote',
            'sort_order' => 1,
        ]);

        $response->assertSessionHasErrors('employment_type');
    }

    /** @test */
    public function create_requires_valid_location_type(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/job-positions', [
            'title' => 'Test Position',
            'employment_type' => 'full-time',
            'location_type' => 'invalid-type',
            'sort_order' => 1,
        ]);

        $response->assertSessionHasErrors('location_type');
    }

    /** @test */
    public function linkedin_url_must_be_valid_url(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/job-positions', [
            'title' => 'Test Position',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'linkedin_url' => 'not-a-valid-url',
            'sort_order' => 1,
        ]);

        $response->assertSessionHasErrors('linkedin_url');
    }

    /** @test */
    public function job_position_model_has_active_scope(): void
    {
        JobPosition::create([
            'title' => 'Active',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        JobPosition::create([
            'title' => 'Inactive',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $active = JobPosition::active()->get();

        $this->assertCount(1, $active);
        $this->assertEquals('Active', $active->first()->title);
    }

    /** @test */
    public function job_position_model_has_featured_scope(): void
    {
        JobPosition::create([
            'title' => 'Featured',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        JobPosition::create([
            'title' => 'Regular',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $featured = JobPosition::featured()->get();

        $this->assertCount(1, $featured);
        $this->assertEquals('Featured', $featured->first()->title);
    }

    /** @test */
    public function job_position_model_has_ordered_scope(): void
    {
        JobPosition::create([
            'title' => 'Third',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'sort_order' => 3,
        ]);

        JobPosition::create([
            'title' => 'First',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'sort_order' => 1,
        ]);

        $ordered = JobPosition::ordered()->get();

        $this->assertEquals('First', $ordered->first()->title);
        $this->assertEquals('Third', $ordered->last()->title);
    }

    /** @test */
    public function job_position_returns_correct_employment_type_label(): void
    {
        $position = JobPosition::create([
            'title' => 'Test',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'sort_order' => 1,
        ]);

        $this->assertEquals('Full Time', $position->employment_type_label);
    }

    /** @test */
    public function job_position_returns_correct_location_type_label(): void
    {
        $position = JobPosition::create([
            'title' => 'Test',
            'employment_type' => 'full-time',
            'location_type' => 'hybrid',
            'sort_order' => 1,
        ]);

        $this->assertEquals('Hybrid', $position->location_type_label);
    }

    /** @test */
    public function job_position_apply_link_prefers_linkedin_url(): void
    {
        $position = JobPosition::create([
            'title' => 'Test',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'linkedin_url' => 'https://linkedin.com/jobs/123',
            'apply_url' => 'https://example.com/apply',
            'sort_order' => 1,
        ]);

        $this->assertEquals('https://linkedin.com/jobs/123', $position->apply_link);
    }

    /** @test */
    public function job_position_apply_link_uses_apply_url_as_fallback(): void
    {
        $position = JobPosition::create([
            'title' => 'Test',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'apply_url' => 'https://example.com/apply',
            'sort_order' => 1,
        ]);

        $this->assertEquals('https://example.com/apply', $position->apply_link);
    }

    /** @test */
    public function job_position_returns_localized_title(): void
    {
        $position = JobPosition::create([
            'title' => 'Developer',
            'title_es' => 'Desarrollador',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'sort_order' => 1,
        ]);

        $this->assertEquals('Developer', $position->getLocalizedTitle('en'));
        $this->assertEquals('Desarrollador', $position->getLocalizedTitle('es'));
    }

    /** @test */
    public function job_position_returns_localized_description(): void
    {
        $position = JobPosition::create([
            'title' => 'Developer',
            'employment_type' => 'full-time',
            'location_type' => 'remote',
            'description' => 'English description',
            'description_es' => 'Descripción en español',
            'sort_order' => 1,
        ]);

        $this->assertEquals('English description', $position->getLocalizedDescription('en'));
        $this->assertEquals('Descripción en español', $position->getLocalizedDescription('es'));
    }
}
