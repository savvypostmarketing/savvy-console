<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the roles for this user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Get the direct permissions for this user
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withPivot('is_denied')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles()->where('slug', $roles)->exists();
        }

        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Super admins have all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check cache first
        $cacheKey = "user_{$this->id}_permissions";
        $permissions = Cache::remember($cacheKey, 300, function () {
            return $this->getAllPermissions();
        });

        // Check if permission is denied
        $deniedPermission = $this->permissions()
            ->where('slug', $permission)
            ->wherePivot('is_denied', true)
            ->exists();

        if ($deniedPermission) {
            return false;
        }

        return in_array($permission, $permissions);
    }

    /**
     * Get all permissions (from roles + direct permissions)
     */
    public function getAllPermissions(): array
    {
        // Get permissions from roles
        $rolePermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->toArray();

        // Get direct permissions (not denied)
        $directPermissions = $this->permissions()
            ->wherePivot('is_denied', false)
            ->pluck('slug')
            ->toArray();

        return array_unique(array_merge($rolePermissions, $directPermissions));
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign role to user
     */
    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([$role->id]);
        $this->clearPermissionCache();
    }

    /**
     * Remove role from user
     */
    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role->id);
            $this->clearPermissionCache();
        }
    }

    /**
     * Sync roles
     */
    public function syncRoles(array $roles): void
    {
        $roleIds = Role::whereIn('slug', $roles)->pluck('id');
        $this->roles()->sync($roleIds);
        $this->clearPermissionCache();
    }

    /**
     * Give direct permission to user
     */
    public function givePermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([
            $permission->id => ['is_denied' => false],
        ]);
        $this->clearPermissionCache();
    }

    /**
     * Deny permission for user
     */
    public function denyPermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([
            $permission->id => ['is_denied' => true],
        ]);
        $this->clearPermissionCache();
    }

    /**
     * Remove direct permission from user
     */
    public function revokePermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
            $this->clearPermissionCache();
        }
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    /**
     * Check if user is admin (super admin or admin)
     */
    public function isAdmin(): bool
    {
        return $this->hasRole([Role::SUPER_ADMIN, Role::ADMIN]);
    }

    /**
     * Get highest role level
     */
    public function getHighestRoleLevel(): int
    {
        return $this->roles()->max('level') ?? 0;
    }

    /**
     * Check if user can manage another user (based on role level)
     */
    public function canManage(User $user): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Can't manage yourself
        if ($this->id === $user->id) {
            return false;
        }

        // Can only manage users with lower role level
        return $this->getHighestRoleLevel() > $user->getHighestRoleLevel();
    }

    /**
     * Clear permission cache
     */
    public function clearPermissionCache(): void
    {
        Cache::forget("user_{$this->id}_permissions");
    }

    /**
     * Get user's primary role
     */
    public function getPrimaryRole(): ?Role
    {
        return $this->roles()->orderBy('level', 'desc')->first();
    }

    /**
     * Scope for users with role
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('slug', $role);
        });
    }

    /**
     * Scope for admins
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->whereIn('slug', [Role::SUPER_ADMIN, Role::ADMIN]);
        });
    }
}
