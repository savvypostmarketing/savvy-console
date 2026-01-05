export interface User {
    id: number;
    name: string;
    email: string;
    roles: string[];
    permissions: string[];
    is_super_admin: boolean;
    is_admin: boolean;
}

export interface AuthState {
    user: User | null;
}

export interface LoginCredentials {
    email: string;
    password: string;
    remember: boolean;
}

export interface LoginResponse {
    success: boolean;
    redirect?: string;
}
