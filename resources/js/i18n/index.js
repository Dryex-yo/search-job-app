import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import id from './locales/id.json';

const messages = {
    en,
    id,
};

const SUPPORTED_LOCALES = ['en', 'id'];
const DEFAULT_LOCALE = 'en';

// Get initial locale from localStorage, sessionStorage, or browser
const getInitialLocale = () => {
    try {
        // Check localStorage first (persistent)
        const stored = localStorage.getItem('app_locale');
        if (stored && SUPPORTED_LOCALES.includes(stored)) {
            return stored;
        }
        
        // Check sessionStorage (for current session)
        const session = sessionStorage.getItem('app_locale');
        if (session && SUPPORTED_LOCALES.includes(session)) {
            return session;
        }
    } catch (e) {
        console.warn('Storage access error:', e);
    }
    
    // Check browser language
    try {
        const browserLang = navigator.language?.split('-')[0]?.toLowerCase();
        if (browserLang && SUPPORTED_LOCALES.includes(browserLang)) {
            return browserLang;
        }
    } catch (e) {
        console.warn('Browser language detection error:', e);
    }
    
    return DEFAULT_LOCALE;
};

const i18n = createI18n({
    legacy: false,
    locale: getInitialLocale(),
    fallbackLocale: DEFAULT_LOCALE,
    messages,
    globalInjection: true,
    missingWarn: false,
    fallbackWarn: false,
});

// Persist locale changes to storage
const setupLocaleWatcher = (instance) => {
    if (typeof window === 'undefined') return;
    
    const originalLocale = instance.global.locale;
    
    Object.defineProperty(instance.global, 'locale', {
        get() {
            return originalLocale.value;
        },
        set(newLocale) {
            if (SUPPORTED_LOCALES.includes(newLocale)) {
                originalLocale.value = newLocale;
                try {
                    localStorage.setItem('app_locale', newLocale);
                    sessionStorage.setItem('app_locale', newLocale);
                    document.documentElement.lang = newLocale;
                    document.documentElement.setAttribute('data-locale', newLocale);
                } catch (e) {
                    console.warn('Failed to persist locale:', e);
                }
            }
        },
    });
};

setupLocaleWatcher(i18n);

export default i18n;
