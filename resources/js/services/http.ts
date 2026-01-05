import axios, { AxiosError, AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import type { ApiError } from '@/interfaces';

const DEFAULT_TIMEOUT = 30000;
const MAX_RETRIES = 3;
const RETRY_DELAY = 1000;

/**
 * Create configured axios instance
 */
function createHttpClient(): AxiosInstance {
    const client = axios.create({
        baseURL: '/api',
        timeout: DEFAULT_TIMEOUT,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        withCredentials: true,
    });

    // Request interceptor
    client.interceptors.request.use(
        (config) => {
            // Add CSRF token if available
            const token = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');
            if (token) {
                config.headers['X-CSRF-TOKEN'] = token;
            }
            return config;
        },
        (error) => Promise.reject(error)
    );

    // Response interceptor
    client.interceptors.response.use(
        (response) => response,
        async (error: AxiosError) => {
            const originalRequest = error.config as AxiosRequestConfig & { _retry?: number };

            // Handle retry logic for network errors
            const shouldRetry =
                error.code === 'ECONNABORTED' ||
                error.code === 'ERR_NETWORK' ||
                (error.response?.status && error.response.status >= 500);

            if (shouldRetry && originalRequest) {
                const retryCount = originalRequest._retry ?? 0;

                if (retryCount < MAX_RETRIES) {
                    originalRequest._retry = retryCount + 1;
                    await delay(RETRY_DELAY * (retryCount + 1));
                    return client(originalRequest);
                }
            }

            // Handle 401 - redirect to login
            if (error.response?.status === 401) {
                window.location.href = '/login';
            }

            return Promise.reject(normalizeError(error));
        }
    );

    return client;
}

/**
 * Delay helper for retries
 */
function delay(ms: number): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Normalize axios error to ApiError
 */
function normalizeError(error: AxiosError): ApiError {
    const response = error.response;

    if (!response) {
        return {
            message: 'Network error. Please check your connection.',
            status: 0,
        };
    }

    const data = response.data as Record<string, unknown>;

    return {
        message: (data.message as string) ?? 'An unexpected error occurred',
        errors: data.errors as Record<string, string[]> | undefined,
        status: response.status,
    };
}

/**
 * HTTP client singleton
 */
export const http = createHttpClient();

/**
 * Type-safe GET request
 */
export async function get<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
    const response: AxiosResponse<T> = await http.get(url, config);
    return response.data;
}

/**
 * Type-safe POST request
 */
export async function post<T, D = unknown>(
    url: string,
    data?: D,
    config?: AxiosRequestConfig
): Promise<T> {
    const response: AxiosResponse<T> = await http.post(url, data, config);
    return response.data;
}

/**
 * Type-safe PUT request
 */
export async function put<T, D = unknown>(
    url: string,
    data?: D,
    config?: AxiosRequestConfig
): Promise<T> {
    const response: AxiosResponse<T> = await http.put(url, data, config);
    return response.data;
}

/**
 * Type-safe PATCH request
 */
export async function patch<T, D = unknown>(
    url: string,
    data?: D,
    config?: AxiosRequestConfig
): Promise<T> {
    const response: AxiosResponse<T> = await http.patch(url, data, config);
    return response.data;
}

/**
 * Type-safe DELETE request
 */
export async function del<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
    const response: AxiosResponse<T> = await http.delete(url, config);
    return response.data;
}

/**
 * Create cancel token source
 */
export function createCancelToken() {
    return axios.CancelToken.source();
}

/**
 * Check if error is a cancel error
 */
export function isCancel(error: unknown): boolean {
    return axios.isCancel(error);
}
