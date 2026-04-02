import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import id from './locales/id.json';

const messages = {
    en,
    id,
};

// Get initial locale from localStorage or browser
const getInitialLocale = () => {
    const stored = localStorage.getItem('app_locale');
    if (stored) return stored;
    
    const browserLang = navigator.language.split('-')[0];
    return ['en', 'id'].includes(browserLang) ? browserLang : 'en';
};

const i18n = createI18n({
    legacy: false, // Use composition API mode
    locale: getInitialLocale(),
    fallbackLocale: 'en',
    messages,
    globalInjection: true,
    missingWarn: false,
    fallbackWarn: false,
});

export default i18n;
