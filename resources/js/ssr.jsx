import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import ReactDOMServer from 'react-dom/server';
import { route as ziggyRoute } from 'ziggy-js';

createServer((page) =>
    createInertiaApp({
        page,
        title: (title) => title || 'AVA VPN',
        resolve: (name) => {
            const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
            return pages[`./Pages/${name}.jsx`];
        },
        setup({ App, props }) {
            const ziggyConfig = props.initialPage.props.ziggy;

            globalThis.route = (name, params, absolute) =>
                ziggyRoute(name, params, absolute, {
                    ...ziggyConfig,
                    location: new URL(ziggyConfig.location),
                });

            return <App {...props} />;
        },
        render: ReactDOMServer.renderToString,
    }),
);
