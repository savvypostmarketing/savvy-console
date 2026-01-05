import { useMemo, useCallback } from 'react';
import { usePage } from '@inertiajs/react';
import type { PageProps, User } from '@/interfaces';
import { hasPermission, hasAnyPermission, hasAllPermissions, isSuperAdmin, isAdmin } from '@/utils';

interface UsePermissionsReturn {
    user: User | null;
    currentUserId: number | null;
    checkPermission: (permission: string) => boolean;
    checkAnyPermission: (permissions: string[]) => boolean;
    checkAllPermissions: (permissions: string[]) => boolean;
    isSuperAdmin: boolean;
    isAdmin: boolean;
}

/**
 * Hook for checking user permissions
 */
export function usePermissions(): UsePermissionsReturn {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;

    const checkPermission = useCallback(
        (permission: string): boolean => hasPermission(user, permission),
        [user]
    );

    const checkAnyPermission = useCallback(
        (permissions: string[]): boolean => hasAnyPermission(user, permissions),
        [user]
    );

    const checkAllPermissions = useCallback(
        (permissions: string[]): boolean => hasAllPermissions(user, permissions),
        [user]
    );

    const currentUserId = useMemo(() => user?.id ?? null, [user]);
    const userIsSuperAdmin = useMemo(() => isSuperAdmin(user), [user]);
    const userIsAdmin = useMemo(() => isAdmin(user), [user]);

    return {
        user,
        currentUserId,
        checkPermission,
        checkAnyPermission,
        checkAllPermissions,
        isSuperAdmin: userIsSuperAdmin,
        isAdmin: userIsAdmin,
    };
}
