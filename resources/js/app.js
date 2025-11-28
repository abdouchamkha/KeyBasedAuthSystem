import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import axios from 'axios';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Add this before creating the Inertia app
axios.interceptors.response.use(
    response => response,
    error => {
        // Check if the error is from Cloudflare challenge (403 status)
        if (error.response?.status === 403) {
            // Check for Cloudflare specific headers
            const cfHeaders = error.response?.headers;
            const isCloudfareChallenge = 
                cfHeaders?.['cf-mitigated'] === 'challenge' || 
                cfHeaders?.['cf-chl-out'];

            if (isCloudfareChallenge) {
                // Perform a full page reload to handle the challenge
                window.location.href = window.location.href;
                // Return a rejected promise to maintain the error chain
                return Promise.reject(error);
            }
        }
        return Promise.reject(error);
    }
);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
