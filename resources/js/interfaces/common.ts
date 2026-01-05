export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface FlashMessages {
    success?: string;
    error?: string;
}

export interface PageProps {
    auth: {
        user: import('./auth').User | null;
    };
    flash: FlashMessages;
    [key: string]: unknown;
}

export interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
    status: number;
}

export interface SelectOption<T = string> {
    label: string;
    value: T;
}
