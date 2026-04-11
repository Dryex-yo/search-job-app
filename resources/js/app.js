import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, defineAsyncComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useDarkMode } from './Composables/useDarkMode';
import { useLanguage } from './Composables/useLanguage';
import i18n from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize dark mode
const { initializeDarkMode } = useDarkMode();
initializeDarkMode();

// Restore and maintain language state
const restoreLanguageState = () => {
    try {
        // Check localStorage first (persistent)
        const stored = localStorage.getItem('app_locale');
        if (stored && ['en', 'id'].includes(stored)) {
            i18n.global.locale.value = stored;
            document.documentElement.lang = stored;
            document.documentElement.setAttribute('data-locale', stored);
            return;
        }

        // Check sessionStorage (for current session)
        const session = sessionStorage.getItem('app_locale');
        if (session && ['en', 'id'].includes(session)) {
            i18n.global.locale.value = session;
            document.documentElement.lang = session;
            document.documentElement.setAttribute('data-locale', session);
            return;
        }

        // Check Inertia props for server-set locale
        if (window.inertiaProps && window.inertiaProps.locale) {
            const serverLocale = window.inertiaProps.locale;
            if (['en', 'id'].includes(serverLocale)) {
                i18n.global.locale.value = serverLocale;
                localStorage.setItem('app_locale', serverLocale);
                document.documentElement.lang = serverLocale;
                document.documentElement.setAttribute('data-locale', serverLocale);
                return;
            }
        }

        // Check browser language
        try {
            const browserLang = navigator.language?.split('-')[0]?.toLowerCase();
            if (browserLang && ['en', 'id'].includes(browserLang)) {
                i18n.global.locale.value = browserLang;
                localStorage.setItem('app_locale', browserLang);
                document.documentElement.lang = browserLang;
                document.documentElement.setAttribute('data-locale', browserLang);
                return;
            }
        } catch (e) {
            console.warn('Browser language detection error:', e);
        }

        // Default to English
        i18n.global.locale.value = 'en';
        document.documentElement.lang = 'en';
        document.documentElement.setAttribute('data-locale', 'en');
    } catch (e) {
        console.warn('Error restoring language state:', e);
    }
};

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

        // Restore language state on app mount
        restoreLanguageState();

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
