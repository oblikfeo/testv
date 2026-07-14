import './bootstrap';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route as ziggyRoute } from 'ziggy-js';

createInertiaApp({
    // Every page sets its own complete, SEO-crafted <title> (mirrors the previous
    // Blade @section('title', '...') pattern) — don't append a global suffix here.
    title: (title) => title || 'AVA VPN',
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const ziggyConfig = props.initialPage.props.ziggy;

        globalThis.route = (name, params, absolute) =>
            ziggyRoute(name, params, absolute, {
                ...ziggyConfig,
                location: new URL(ziggyConfig.location),
            });

        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#e11d3c',
    },
});
