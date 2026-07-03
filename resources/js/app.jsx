import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { CartProvider } from './cart';
import { LangProvider } from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Carnicería';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <LangProvider>
                <CartProvider>
                    <App {...props} />
                </CartProvider>
            </LangProvider>,
        );
    },
    progress: {
        color: '#b91c1c',
    },
});
