import { useEffect, useId } from 'react';
import { usePage } from '@inertiajs/react';
import { useToastController, Toast, ToastTitle } from '@fluentui/react-components';
import { createElement } from 'react';
import type { PageProps } from '@/interfaces';

interface UseFlashReturn {
    toasterId: string;
}

/**
 * Hook for displaying flash messages as toasts
 */
export function useFlash(): UseFlashReturn {
    const { flash } = usePage<PageProps>().props;
    const toasterId = useId();
    const { dispatchToast } = useToastController(toasterId);

    useEffect(() => {
        if (flash.success) {
            dispatchToast(
                createElement(Toast, null, createElement(ToastTitle, null, flash.success)),
                { intent: 'success' }
            );
        }

        if (flash.error) {
            dispatchToast(
                createElement(Toast, null, createElement(ToastTitle, null, flash.error)),
                { intent: 'error' }
            );
        }
    }, [flash, dispatchToast]);

    return { toasterId };
}
