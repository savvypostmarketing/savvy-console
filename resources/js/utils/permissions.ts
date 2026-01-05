import type { User } from '@/interfaces';

/**
 * Check if user has a specific permission
 */
export function hasPermission(user: User | null, permission: string): boolean {
    if (!user) {
        return false;
    }
    if (user.is_super_admin) {
        return true;
    }
    return user.permissions.includes(permission);
}

/**
 * Check if user has any of the given permissions
 */
export function hasAnyPermission(user: User | null, permissions: string[]): boolean {
    if (!user) {
        return false;
    }
    if (user.is_super_admin) {
        return true;
    }
    return permissions.some((p) => user.permissions.includes(p));
}

/**
 * Check if user has all of the given permissions
 */
export function hasAllPermissions(user: User | null, permissions: string[]): boolean {
    if (!user) {
        return false;
    }
    if (user.is_super_admin) {
        return true;
    }
    return permissions.every((p) => user.permissions.includes(p));
}

/**
 * Check if user has a specific role
 */
export function hasRole(user: User | null, role: string): boolean {
    if (!user) {
        return false;
    }
    return user.roles.includes(role);
}

/**
 * Check if user is super admin
 */
export function isSuperAdmin(user: User | null): boolean {
    return user?.is_super_admin ?? false;
}

/**
 * Check if user is any type of admin
 */
export function isAdmin(user: User | null): boolean {
    return user?.is_admin ?? false;
}
