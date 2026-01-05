/**
 * Check if value is empty (null, undefined, empty string, empty array)
 */
export function isEmpty(value: unknown): boolean {
    if (value === null || value === undefined) {
        return true;
    }
    if (typeof value === 'string') {
        return value.trim() === '';
    }
    if (Array.isArray(value)) {
        return value.length === 0;
    }
    if (typeof value === 'object') {
        return Object.keys(value).length === 0;
    }
    return false;
}

/**
 * Check if value is a valid email
 */
export function isValidEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Check if password meets minimum requirements
 */
export function isValidPassword(password: string, minLength = 8): boolean {
    return password.length >= minLength;
}

/**
 * Clean object by removing empty values
 */
export function cleanObject<T extends Record<string, unknown>>(obj: T): Partial<T> {
    const result: Partial<T> = {};

    for (const key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
            const value = obj[key];
            if (!isEmpty(value)) {
                result[key] = value;
            }
        }
    }

    return result;
}

/**
 * Build URL search params from object
 */
export function buildSearchParams(params: Record<string, string | undefined>): string {
    const searchParams = new URLSearchParams();

    for (const [key, value] of Object.entries(params)) {
        if (value !== undefined && value !== '') {
            searchParams.append(key, value);
        }
    }

    return searchParams.toString();
}
