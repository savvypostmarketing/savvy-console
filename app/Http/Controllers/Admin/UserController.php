<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request): Response
    {
        $currentUser = $request->user();

        $query = User::with('roles');

        // Non-super admins can only see users with lower or equal role levels + themselves
        if (!$currentUser->isSuperAdmin()) {
            $maxLevel = $currentUser->getHighestRoleLevel();
            $query->where(function ($q) use ($maxLevel, $currentUser) {
                // Always include the current user
                $q->where('id', $currentUser->id)
                    // Users without roles
                    ->orWhereDoesntHave('roles')
                    // Users whose highest role level is <= current user's level (excluding super-admins)
                    ->orWhere(function ($subQ) use ($maxLevel) {
                        $subQ->whereDoesntHave('roles', function ($roleQ) {
                            $roleQ->where('slug', Role::SUPER_ADMIN);
                        })
                        ->whereRaw('(SELECT COALESCE(MAX(r.level), 0) FROM roles r INNER JOIN role_user ru ON r.id = ru.role_id WHERE ru.user_id = users.id) <= ?', [$maxLevel]);
                    });
            });
        }

        $users = $query->latest()
            ->paginate(20)
            ->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->map(fn ($role) => [
                    'name' => $role->name,
                    'slug' => $role->slug,
                ]),
                'is_super_admin' => $user->isSuperAdmin(),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show form for creating a new user
     */
    public function create(Request $request): Response
    {
        $currentUser = $request->user();

        // Get roles the current user can assign
        $roles = Role::when(!$currentUser->isSuperAdmin(), function ($query) use ($currentUser) {
            $query->where('level', '<', $currentUser->getHighestRoleLevel());
        })
        ->byLevel()
        ->get()
        ->map(fn ($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'level' => $role->level,
        ]);

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,slug'],
        ]);

        // Validate that user can assign these roles
        if (!empty($validated['roles'])) {
            $maxAllowedLevel = $currentUser->isSuperAdmin() ? 999 : $currentUser->getHighestRoleLevel() - 1;
            $hasInvalidRole = Role::whereIn('slug', $validated['roles'])
                ->where('level', '>', $maxAllowedLevel)
                ->exists();

            if ($hasInvalidRole) {
                return back()->withErrors([
                    'roles' => 'You cannot assign roles with a level equal to or higher than your own.',
                ]);
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show form for editing a user
     */
    public function edit(Request $request, User $user): Response
    {
        $currentUser = $request->user();

        // Check if current user can manage this user
        if (!$currentUser->canManage($user)) {
            abort(403, 'You do not have permission to edit this user.');
        }

        // Get roles the current user can assign
        $roles = Role::when(!$currentUser->isSuperAdmin(), function ($query) use ($currentUser) {
            $query->where('level', '<', $currentUser->getHighestRoleLevel());
        })
        ->byLevel()
        ->get()
        ->map(fn ($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'level' => $role->level,
        ]);

        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('slug'),
            ],
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Check if current user can manage this user
        if (!$currentUser->canManage($user)) {
            abort(403, 'You do not have permission to edit this user.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,slug'],
        ]);

        // Validate that user can assign these roles
        if (!empty($validated['roles'])) {
            $maxAllowedLevel = $currentUser->isSuperAdmin() ? 999 : $currentUser->getHighestRoleLevel() - 1;
            $hasInvalidRole = Role::whereIn('slug', $validated['roles'])
                ->where('level', '>', $maxAllowedLevel)
                ->exists();

            if ($hasInvalidRole) {
                return back()->withErrors([
                    'roles' => 'You cannot assign roles with a level equal to or higher than your own.',
                ]);
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        $user->syncRoles($validated['roles'] ?? []);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Prevent self-deletion
        if ($currentUser->id === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        // Check if current user can manage this user
        if (!$currentUser->canManage($user)) {
            abort(403, 'You do not have permission to delete this user.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
