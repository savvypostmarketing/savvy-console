import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { FluentProvider, webLightTheme } from '@fluentui/react-components';

void createInertiaApp({
    title: (title) => `${title} - Savvy Admin`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
        return pages[`./Pages/${name}.tsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <FluentProvider theme={webLightTheme}>
                <App {...props} />
            </FluentProvider>
        );
    },
    progress: {
        color: '#0078d4',
    },
});
