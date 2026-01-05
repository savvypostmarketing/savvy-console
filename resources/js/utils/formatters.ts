import type { LeadStatus } from '@/interfaces';

/**
 * Format date to locale string
 */
export function formatDate(date: string | Date, locale = 'en-US'): string {
    const d = typeof date === 'string' ? new Date(date) : date;
    return d.toLocaleDateString(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

/**
 * Format date with time
 */
export function formatDateTime(date: string | Date, locale = 'en-US'): string {
    const d = typeof date === 'string' ? new Date(date) : date;
    return d.toLocaleString(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * Format relative time (e.g., "2 hours ago")
 */
export function formatRelativeTime(date: string | Date): string {
    const d = typeof date === 'string' ? new Date(date) : date;
    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffDays > 0) {
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    }
    if (diffHours > 0) {
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    }
    if (diffMins > 0) {
        return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    }
    return 'Just now';
}

/**
 * Truncate text with ellipsis
 */
export function truncate(text: string, maxLength: number): string {
    if (text.length <= maxLength) {
        return text;
    }
    return `${text.substring(0, maxLength)}...`;
}

/**
 * Get status display color
 */
export function getStatusColor(
    status: LeadStatus
): 'informative' | 'success' | 'warning' | 'danger' | 'important' {
    const colorMap: Record<
        LeadStatus,
        'informative' | 'success' | 'warning' | 'danger' | 'important'
    > = {
        new: 'informative',
        contacted: 'warning',
        qualified: 'important',
        converted: 'success',
        lost: 'danger',
    };
    return colorMap[status];
}

/**
 * Format array as comma-separated string with limit
 */
export function formatArrayWithLimit(arr: string[] | null | undefined, limit: number): string {
    if (!arr || arr.length === 0) {
        return '-';
    }

    const visible = arr.slice(0, limit);
    const remaining = arr.length - limit;

    if (remaining > 0) {
        return `${visible.join(', ')} +${remaining}`;
    }
    return visible.join(', ');
}
