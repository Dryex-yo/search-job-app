import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, defineAsyncComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useDarkMode } from './Composables/useDarkMode';
import i18n from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize dark mode
const { initializeDarkMode } = useDarkMode();
initializeDarkMode();

// Optimize build with lazy code splitting
const pageLoader = name =>
    resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue', { eager: false }),
    );

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => pageLoader(name),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(ZiggyVue)
            .use(i18n);

        // Add performance tracking
        if (import.meta.env.DEV) {
            app.config.performance = true;
        }

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
        delay: 250,
        includeCSS: true,
        showSpinner: true,
    },
});

// Enable Web Workers for heavy computations (optional)
if ('Worker' in window) {
    // Workers can be added here if needed
}
