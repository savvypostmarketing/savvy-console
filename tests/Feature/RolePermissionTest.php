<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::SUPER_ADMIN);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->hasPermission('any-permission'));
        $this->assertTrue($user->hasPermission('nonexistent-permission'));
    }

    public function test_admin_has_assigned_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ADMIN);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasPermission('view-leads'));
        $this->assertTrue($user->hasPermission('edit-leads'));
        $this->assertTrue($user->hasPermission('delete-leads'));
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::USER);
        $user->assignRole(Role::MANAGER);

        $this->assertTrue($user->hasRole([Role::USER, Role::MANAGER]));
        $this->assertEquals(2, $user->roles->count());
    }

    public function test_user_can_have_direct_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::USER);

        // Give direct permission
        $user->givePermission('export-leads');

        $this->assertTrue($user->hasPermission('export-leads'));
    }

    public function test_denied_permission_overrides_role_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ADMIN);

        // Admin has edit-leads permission, but we deny it
        $user->denyPermission('edit-leads');

        $this->assertFalse($user->hasPermission('edit-leads'));
    }

    public function test_role_level_hierarchy(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::SUPER_ADMIN);

        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $manager = User::factory()->create();
        $manager->assignRole(Role::MANAGER);

        // Super admin can manage everyone
        $this->assertTrue($superAdmin->canManage($admin));
        $this->assertTrue($superAdmin->canManage($manager));

        // Admin can manage manager
        $this->assertTrue($admin->canManage($manager));

        // Admin cannot manage super admin
        $this->assertFalse($admin->canManage($superAdmin));

        // Manager cannot manage admin
        $this->assertFalse($manager->canManage($admin));
    }

    public function test_user_cannot_manage_themselves(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ADMIN);

        $this->assertFalse($user->canManage($user));
    }

    public function test_role_permissions_are_synced(): void
    {
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'level' => 20,
        ]);

        $role->syncPermissions(['view-leads', 'edit-leads']);

        $this->assertEquals(2, $role->permissions->count());
        $this->assertTrue($role->hasPermission('view-leads'));
        $this->assertTrue($role->hasPermission('edit-leads'));
    }

    public function test_permission_cache_is_cleared_on_role_change(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::USER);

        // Access permissions to cache them
        $user->hasPermission('view-leads');

        // Assign new role
        $user->assignRole(Role::ADMIN);

        // Should have new permissions
        $this->assertTrue($user->hasPermission('edit-leads'));
    }

    public function test_user_with_permission_can_access_protected_route(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ADMIN);

        $response = $this->actingAs($user)->get('/admin/leads');
        $response->assertStatus(200);
    }

    public function test_user_without_permission_cannot_access_protected_route(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::USER);

        // User role doesn't have delete-leads permission
        $response = $this->actingAs($user)->delete('/admin/leads/1');
        $response->assertStatus(403);
    }

    public function test_all_permissions_are_returned_correctly(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::MANAGER);
        $user->givePermission('delete-leads'); // Extra permission

        $permissions = $user->getAllPermissions();

        $this->assertContains('view-leads', $permissions);
        $this->assertContains('edit-leads', $permissions);
        $this->assertContains('delete-leads', $permissions);
    }
}
