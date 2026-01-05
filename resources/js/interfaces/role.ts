export interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    level: number;
    is_system: boolean;
    users_count: number;
    permissions: string[];
    created_at: string;
}

export interface RoleFormData {
    name: string;
    description: string;
    level: number;
    permissions: string[];
}

export interface Permission {
    id: number;
    name: string;
    slug: string;
    group: string | null;
    description: string | null;
    roles_count: number;
    created_at: string;
}

export interface PermissionFormData {
    name: string;
    group: string;
    description: string;
}

export interface GroupedPermissions {
    [group: string]: Permission[];
}
