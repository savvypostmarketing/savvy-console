<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions
     */
    public function index(): Response
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->map(fn ($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'group' => $permission->group,
                'description' => $permission->description,
                'roles_count' => $permission->roles_count,
                'created_at' => $permission->created_at->format('Y-m-d H:i:s'),
            ]);

        $groupedPermissions = $permissions->groupBy('group');

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    /**
     * Show form for creating a new permission
     */
    public function create(): Response
    {
        $groups = Permission::distinct()->pluck('group')->filter()->values();

        return Inertia::render('Admin/Permissions/Create', [
            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::slug($validated['name']);

        if (Permission::where('slug', $slug)->exists()) {
            return back()->withErrors([
                'name' => 'A permission with this name already exists.',
            ]);
        }

        Permission::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'group' => $validated['group'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Show form for editing a permission
     */
    public function edit(Permission $permission): Response
    {
        $groups = Permission::distinct()->pluck('group')->filter()->values();

        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'group' => $permission->group,
                'description' => $permission->description,
            ],
            'groups' => $groups,
        ]);
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::slug($validated['name']);

        if (Permission::where('slug', $slug)->where('id', '!=', $permission->id)->exists()) {
            return back()->withErrors([
                'name' => 'A permission with this name already exists.',
            ]);
        }

        $permission->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'group' => $validated['group'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission
     */
    public function destroy(Permission $permission)
    {
        // Detach from all roles first
        $permission->roles()->detach();
        $permission->users()->detach();

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
