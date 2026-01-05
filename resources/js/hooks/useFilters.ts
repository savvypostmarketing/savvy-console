import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import { cleanObject } from '@/utils';

interface UseFiltersOptions<T> {
    initialFilters: T;
    url: string;
}

interface UseFiltersReturn<T> {
    filters: T;
    isFiltering: boolean;
    setFilter: <K extends keyof T>(key: K, value: T[K]) => void;
    applyFilters: () => void;
    resetFilters: () => void;
    handleKeyDown: (e: React.KeyboardEvent) => void;
}

/**
 * Hook for managing filter state and applying filters
 */
export function useFilters<T extends Record<string, string | undefined>>({
    initialFilters,
    url,
}: UseFiltersOptions<T>): UseFiltersReturn<T> {
    const [filters, setFilters] = useState<T>(initialFilters);
    const [isFiltering, setIsFiltering] = useState(false);

    const setFilter = useCallback(<K extends keyof T>(key: K, value: T[K]) => {
        setFilters((prev) => ({ ...prev, [key]: value }));
    }, []);

    const applyFilters = useCallback(() => {
        setIsFiltering(true);
        const cleanedFilters = cleanObject(filters as Record<string, unknown>);

        router.get(url, cleanedFilters as Record<string, string>, {
            preserveState: false,
            preserveScroll: true,
            onFinish: () => setIsFiltering(false),
        });
    }, [filters, url]);

    const resetFilters = useCallback(() => {
        setIsFiltering(true);
        setFilters(initialFilters);

        router.get(
            url,
            {},
            {
                preserveState: false,
                preserveScroll: true,
                onFinish: () => setIsFiltering(false),
            }
        );
    }, [initialFilters, url]);

    const handleKeyDown = useCallback(
        (e: React.KeyboardEvent) => {
            if (e.key === 'Enter') {
                applyFilters();
            }
        },
        [applyFilters]
    );

    return {
        filters,
        isFiltering,
        setFilter,
        applyFilters,
        resetFilters,
        handleKeyDown,
    };
}
