<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestimonialAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminWithPermission;
    protected User $adminWithoutPermission;

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

        // Create testimonial permissions
        $viewPermission = Permission::create([
            'name' => 'View Testimonials',
            'slug' => 'view-testimonials',
            'description' => 'Can view testimonials',
            'group' => 'testimonials',
        ]);

        $createPermission = Permission::create([
            'name' => 'Create Testimonials',
            'slug' => 'create-testimonials',
            'description' => 'Can create testimonials',
            'group' => 'testimonials',
        ]);

        $editPermission = Permission::create([
            'name' => 'Edit Testimonials',
            'slug' => 'edit-testimonials',
            'description' => 'Can edit testimonials',
            'group' => 'testimonials',
        ]);

        $deletePermission = Permission::create([
            'name' => 'Delete Testimonials',
            'slug' => 'delete-testimonials',
            'description' => 'Can delete testimonials',
            'group' => 'testimonials',
        ]);

        // Create super admin user
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superAdminRole);

        // Create admin with testimonial permissions
        $this->adminWithPermission = User::factory()->create();
        $this->adminWithPermission->assignRole($adminRole);
        $adminRole->givePermission($viewPermission);
        $adminRole->givePermission($createPermission);
        $adminRole->givePermission($editPermission);
        $adminRole->givePermission($deletePermission);

        // Create admin without permissions
        $adminWithoutRole = Role::create([
            'name' => 'Limited Admin',
            'slug' => 'limited-admin',
            'description' => 'Limited access',
            'level' => 30,
            'is_system' => false,
        ]);
        $this->adminWithoutPermission = User::factory()->create();
        $this->adminWithoutPermission->assignRole($adminWithoutRole);
    }

    /** @test */
    public function guests_cannot_access_testimonials_admin(): void
    {
        $response = $this->get('/admin/testimonials');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function users_without_permission_cannot_access_testimonials(): void
    {
        $response = $this->actingAs($this->adminWithoutPermission)->get('/admin/testimonials');

        $response->assertStatus(403);
    }

    /** @test */
    public function users_with_permission_can_access_testimonials_index(): void
    {
        $response = $this->actingAs($this->adminWithPermission)->get('/admin/testimonials');

        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_can_access_testimonials_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/testimonials');

        $response->assertStatus(200);
    }

    /** @test */
    public function users_with_permission_can_access_create_page(): void
    {
        $response = $this->actingAs($this->adminWithPermission)->get('/admin/testimonials/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function users_with_permission_can_create_testimonial(): void
    {
        $response = $this->actingAs($this->adminWithPermission)->post('/admin/testimonials', [
            'name' => 'John Doe',
            'role' => 'CEO',
            'company' => 'Acme Inc',
            'quote' => 'Great service!',
            'rating' => 5,
            'source' => 'website',
            'services' => ['website', 'branding'],
            'is_featured' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response->assertRedirect('/admin/testimonials');

        $this->assertDatabaseHas('testimonials', [
            'name' => 'John Doe',
            'role' => 'CEO',
            'company' => 'Acme Inc',
            'quote' => 'Great service!',
            'rating' => 5,
            'source' => 'website',
        ]);
    }

    /** @test */
    public function users_with_permission_can_access_edit_page(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'Test User',
            'quote' => 'Test quote',
            'rating' => 5,
            'source' => 'website',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->adminWithPermission)->get("/admin/testimonials/{$testimonial->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function users_with_permission_can_update_testimonial(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'Original Name',
            'quote' => 'Original quote',
            'rating' => 4,
            'source' => 'website',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->adminWithPermission)->put("/admin/testimonials/{$testimonial->id}", [
            'name' => 'Updated Name',
            'quote' => 'Updated quote',
            'rating' => 5,
            'source' => 'google',
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $response->assertRedirect('/admin/testimonials');

        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'name' => 'Updated Name',
            'quote' => 'Updated quote',
            'rating' => 5,
            'source' => 'google',
        ]);
    }

    /** @test */
    public function users_with_permission_can_delete_testimonial(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'To Delete',
            'quote' => 'Delete me',
            'rating' => 5,
            'source' => 'website',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->adminWithPermission)->delete("/admin/testimonials/{$testimonial->id}");

        $response->assertRedirect('/admin/testimonials');

        // Testimonial uses soft deletes
        $this->assertSoftDeleted('testimonials', [
            'id' => $testimonial->id,
        ]);
    }

    /** @test */
    public function users_with_permission_can_toggle_published(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'Test',
            'quote' => 'Test quote',
            'rating' => 5,
            'source' => 'website',
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->adminWithPermission)->patch("/admin/testimonials/{$testimonial->id}/toggle-published");

        $response->assertRedirect();

        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'is_published' => true,
        ]);
    }

    /** @test */
    public function users_with_permission_can_toggle_featured(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'Test',
            'quote' => 'Test quote',
            'rating' => 5,
            'source' => 'website',
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->adminWithPermission)->patch("/admin/testimonials/{$testimonial->id}/toggle-featured");

        $response->assertRedirect();

        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'is_featured' => true,
        ]);
    }

    /** @test */
    public function create_requires_name(): void
    {
        $response = $this->actingAs($this->adminWithPermission)->post('/admin/testimonials', [
            'quote' => 'Test quote',
            'rating' => 5,
            'source' => 'website',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function create_requires_quote(): void
    {
        $response = $this->actingAs($this->adminWithPermission)->post('/admin/testimonials', [
            'name' => 'Test Name',
            'rating' => 5,
            'source' => 'website',
        ]);

        $response->assertSessionHasErrors('quote');
    }

    /** @test */
    public function create_requires_valid_rating(): void
    {
        $response = $this->actingAs($this->adminWithPermission)->post('/admin/testimonials', [
            'name' => 'Test Name',
            'quote' => 'Test quote',
            'rating' => 6, // Invalid - must be 1-5
            'source' => 'website',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function create_requires_valid_source(): void
    {
        $response = $this->actingAs($this->adminWithPermission)->post('/admin/testimonials', [
            'name' => 'Test Name',
            'quote' => 'Test quote',
            'rating' => 5,
            'source' => 'invalid-source',
        ]);

        $response->assertSessionHasErrors('source');
    }

    /** @test */
    public function testimonial_model_auto_generates_uuid(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'Test UUID',
            'quote' => 'Test quote',
            'rating' => 5,
            'source' => 'website',
            'sort_order' => 1,
        ]);

        $this->assertNotNull($testimonial->uuid);
        $this->assertTrue(Str::isUuid($testimonial->uuid));
    }

    /** @test */
    public function testimonial_model_uses_soft_deletes(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'To Delete',
            'quote' => 'Test quote',
            'rating' => 5,
            'source' => 'website',
            'sort_order' => 1,
        ]);

        $testimonial->delete();

        $this->assertSoftDeleted('testimonials', [
            'id' => $testimonial->id,
        ]);

        $this->assertNull(Testimonial::find($testimonial->id));
        $this->assertNotNull(Testimonial::withTrashed()->find($testimonial->id));
    }

    /** @test */
    public function published_scope_filters_testimonials(): void
    {
        Testimonial::create([
            'name' => 'Published',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Draft',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'is_published' => false,
            'sort_order' => 2,
        ]);

        $published = Testimonial::published()->get();

        $this->assertCount(1, $published);
        $this->assertEquals('Published', $published->first()->name);
    }

    /** @test */
    public function featured_scope_filters_testimonials(): void
    {
        Testimonial::create([
            'name' => 'Featured',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Regular',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $featured = Testimonial::featured()->get();

        $this->assertCount(1, $featured);
        $this->assertEquals('Featured', $featured->first()->name);
    }

    /** @test */
    public function ordered_scope_orders_by_sort_order(): void
    {
        Testimonial::create([
            'name' => 'Third',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'sort_order' => 3,
        ]);

        Testimonial::create([
            'name' => 'First',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'sort_order' => 1,
        ]);

        $ordered = Testimonial::ordered()->get();

        $this->assertEquals('First', $ordered->first()->name);
        $this->assertEquals('Third', $ordered->last()->name);
    }

    /** @test */
    public function by_source_scope_filters_by_source(): void
    {
        Testimonial::create([
            'name' => 'Google Review',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'google',
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Website Review',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'sort_order' => 2,
        ]);

        $google = Testimonial::bySource('google')->get();

        $this->assertCount(1, $google);
        $this->assertEquals('Google Review', $google->first()->name);
    }

    /** @test */
    public function for_service_scope_filters_by_service(): void
    {
        Testimonial::create([
            'name' => 'Web Client',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'services' => ['website', 'seo'],
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'name' => 'Branding Client',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'services' => ['branding'],
            'sort_order' => 2,
        ]);

        $webClients = Testimonial::forService('website')->get();

        $this->assertCount(1, $webClients);
        $this->assertEquals('Web Client', $webClients->first()->name);
    }

    /** @test */
    public function services_are_cast_to_array(): void
    {
        $testimonial = Testimonial::create([
            'name' => 'Test',
            'quote' => 'Test',
            'rating' => 5,
            'source' => 'website',
            'services' => ['website', 'branding', 'seo'],
            'sort_order' => 1,
        ]);

        $this->assertIsArray($testimonial->services);
        $this->assertCount(3, $testimonial->services);
        $this->assertContains('website', $testimonial->services);
    }
}
