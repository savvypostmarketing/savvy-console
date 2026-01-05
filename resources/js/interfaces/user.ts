export interface UserListItem {
    id: number;
    name: string;
    email: string;
    roles: UserRole[];
    is_super_admin: boolean;
    created_at: string;
}

export interface UserRole {
    name: string;
    slug: string;
}

export interface UserFormData {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

export interface RoleOption {
    id: number;
    name: string;
    slug: string;
    level: number;
}
