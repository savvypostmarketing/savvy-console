<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions grouped by category
        $permissions = [
            // Admin Panel Access
            'admin' => [
                ['name' => 'Access Admin Panel', 'slug' => 'access-admin-panel', 'description' => 'Can access the admin panel'],
            ],

            // Leads Management
            'leads' => [
                ['name' => 'View Leads', 'slug' => 'view-leads', 'description' => 'Can view leads list and details'],
                ['name' => 'Create Leads', 'slug' => 'create-leads', 'description' => 'Can create new leads manually'],
                ['name' => 'Edit Leads', 'slug' => 'edit-leads', 'description' => 'Can edit lead status and notes'],
                ['name' => 'Delete Leads', 'slug' => 'delete-leads', 'description' => 'Can delete leads'],
                ['name' => 'Export Leads', 'slug' => 'export-leads', 'description' => 'Can export leads to CSV'],
            ],

            // Users Management
            'users' => [
                ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'Can view users list'],
                ['name' => 'Create Users', 'slug' => 'create-users', 'description' => 'Can create new users'],
                ['name' => 'Edit Users', 'slug' => 'edit-users', 'description' => 'Can edit user information'],
                ['name' => 'Delete Users', 'slug' => 'delete-users', 'description' => 'Can delete users'],
            ],

            // Roles Management
            'roles' => [
                ['name' => 'View Roles', 'slug' => 'view-roles', 'description' => 'Can view roles list'],
                ['name' => 'Create Roles', 'slug' => 'create-roles', 'description' => 'Can create new roles'],
                ['name' => 'Edit Roles', 'slug' => 'edit-roles', 'description' => 'Can edit role permissions'],
                ['name' => 'Delete Roles', 'slug' => 'delete-roles', 'description' => 'Can delete roles'],
            ],

            // Settings
            'settings' => [
                ['name' => 'View Settings', 'slug' => 'view-settings', 'description' => 'Can view application settings'],
                ['name' => 'Edit Settings', 'slug' => 'edit-settings', 'description' => 'Can modify application settings'],
            ],

            // Portfolio Management
            'portfolio' => [
                ['name' => 'View Portfolio', 'slug' => 'view-portfolio', 'description' => 'Can view portfolio list and details'],
                ['name' => 'Create Portfolio', 'slug' => 'create-portfolio', 'description' => 'Can create new portfolio entries'],
                ['name' => 'Edit Portfolio', 'slug' => 'edit-portfolio', 'description' => 'Can edit portfolio entries'],
                ['name' => 'Delete Portfolio', 'slug' => 'delete-portfolio', 'description' => 'Can delete portfolio entries'],
            ],

            // Testimonials Management
            'testimonials' => [
                ['name' => 'View Testimonials', 'slug' => 'view-testimonials', 'description' => 'Can view testimonials list and details'],
                ['name' => 'Create Testimonials', 'slug' => 'create-testimonials', 'description' => 'Can create new testimonials'],
                ['name' => 'Edit Testimonials', 'slug' => 'edit-testimonials', 'description' => 'Can edit testimonials'],
                ['name' => 'Delete Testimonials', 'slug' => 'delete-testimonials', 'description' => 'Can delete testimonials'],
            ],
        ];

        // Create all permissions
        foreach ($permissions as $group => $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                Permission::firstOrCreate(
                    ['slug' => $permission['slug']],
                    [
                        'name' => $permission['name'],
                        'group' => $group,
                        'description' => $permission['description'],
                    ]
                );
            }
        }

        // Create system roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => Role::SUPER_ADMIN,
                'description' => 'Full system access with all permissions',
                'level' => 100,
                'is_system' => true,
                'permissions' => [], // Super admin bypasses permission checks
            ],
            [
                'name' => 'Admin',
                'slug' => Role::ADMIN,
                'description' => 'Administrative access with most permissions',
                'level' => 90,
                'is_system' => true,
                'permissions' => [
                    'access-admin-panel',
                    'view-leads', 'create-leads', 'edit-leads', 'delete-leads', 'export-leads',
                    'view-users', 'create-users', 'edit-users', 'delete-users',
                    'view-roles', 'create-roles', 'edit-roles',
                    'view-settings', 'edit-settings',
                    'view-portfolio', 'create-portfolio', 'edit-portfolio', 'delete-portfolio',
                    'view-testimonials', 'create-testimonials', 'edit-testimonials', 'delete-testimonials',
                ],
            ],
            [
                'name' => 'Manager',
                'slug' => Role::MANAGER,
                'description' => 'Manager with access to leads and limited user management',
                'level' => 50,
                'is_system' => true,
                'permissions' => [
                    'access-admin-panel',
                    'view-leads', 'edit-leads', 'export-leads',
                    'view-users',
                    'view-roles',
                    'view-portfolio', 'edit-portfolio',
                    'view-testimonials', 'edit-testimonials',
                ],
            ],
            [
                'name' => 'User',
                'slug' => Role::USER,
                'description' => 'Basic user with limited access',
                'level' => 10,
                'is_system' => true,
                'permissions' => [
                    'access-admin-panel',
                    'view-leads',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissionSlugs = $roleData['permissions'];
            unset($roleData['permissions']);

            // Use updateOrCreate to ensure role data is always up to date
            $role = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Always sync permissions for system roles to ensure new permissions are added
            if ($role->is_system && !empty($permissionSlugs)) {
                $role->syncPermissions($permissionSlugs);
            }
        }

        $this->command->info('Roles and permissions seeded successfully!');

        // Log summary
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Permissions', Permission::count()],
                ['Roles', Role::count()],
            ]
        );
    }
}
