<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(): Response
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->byLevel()
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'level' => $role->level,
                'is_system' => $role->is_system,
                'users_count' => $role->users_count,
                'permissions' => $role->permissions->pluck('slug'),
                'created_at' => $role->created_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show form for creating a new role
     */
    public function create(): Response
    {
        $permissions = Permission::getGrouped();

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['nullable', 'string', 'max:500'],
            'level' => ['required', 'integer', 'min:0', 'max:99'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,slug'],
        ]);

        // Ensure user can only create roles with lower level than their own
        $user = $request->user();
        if (!$user->isSuperAdmin() && $validated['level'] >= $user->getHighestRoleLevel()) {
            return back()->withErrors([
                'level' => 'You cannot create a role with a level equal to or higher than your own.',
            ]);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'level' => $validated['level'],
            'is_system' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show form for editing a role
     */
    public function edit(Role $role): Response
    {
        $permissions = Permission::getGrouped();

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'level' => $role->level,
                'is_system' => $role->is_system,
                'permissions' => $role->permissions->pluck('slug'),
            ],
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $user = $request->user();

        // Prevent editing system roles unless super admin
        if ($role->is_system && !$user->isSuperAdmin()) {
            return back()->withErrors([
                'role' => 'System roles can only be modified by super administrators.',
            ]);
        }

        // Prevent editing roles with higher or equal level
        if (!$user->isSuperAdmin() && $role->level >= $user->getHighestRoleLevel()) {
            return back()->withErrors([
                'role' => 'You cannot edit a role with a level equal to or higher than your own.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:500'],
            'level' => ['required', 'integer', 'min:0', 'max:99'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,slug'],
        ]);

        // Ensure user can only set level lower than their own
        if (!$user->isSuperAdmin() && $validated['level'] >= $user->getHighestRoleLevel()) {
            return back()->withErrors([
                'level' => 'You cannot set a role level equal to or higher than your own.',
            ]);
        }

        $role->update([
            'name' => $validated['name'],
            'slug' => $role->is_system ? $role->slug : Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'level' => $role->is_system ? $role->level : $validated['level'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        // Clear permission cache for all users with this role
        $role->users->each(fn ($user) => $user->clearPermissionCache());

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Request $request, Role $role)
    {
        $user = $request->user();

        // Prevent deleting system roles
        if ($role->is_system) {
            return back()->withErrors([
                'role' => 'System roles cannot be deleted.',
            ]);
        }

        // Prevent deleting roles with higher or equal level
        if (!$user->isSuperAdmin() && $role->level >= $user->getHighestRoleLevel()) {
            return back()->withErrors([
                'role' => 'You cannot delete a role with a level equal to or higher than your own.',
            ]);
        }

        // Clear permission cache for all users with this role before deleting
        $role->users->each(fn ($user) => $user->clearPermissionCache());

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
