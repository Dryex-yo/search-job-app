import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

export function useLanguage() {
    const { locale } = useI18n();
    
    const availableLanguages = [
        { code: 'en', name: 'English', flag: '🇺🇸' },
        { code: 'id', name: 'Bahasa Indonesia', flag: '🇮🇩' }
    ];

    // Use computed to ensure reactivity
    const currentLanguage = computed(() => locale.value);

    const setLanguage = (languageCode) => {
        if (availableLanguages.some(lang => lang.code === languageCode)) {
            locale.value = languageCode;
            localStorage.setItem('app_locale', languageCode);
            document.documentElement.lang = languageCode;
            document.documentElement.setAttribute('data-lang', languageCode);
        }
    };

    const getAvailableLanguages = () => availableLanguages;
    
    const getCurrentLanguage = computed(() => {
        return availableLanguages.find(lang => lang.code === locale.value);
    });

    return {
        currentLanguage,
        availableLanguages,
        setLanguage,
        getAvailableLanguages,
        getCurrentLanguage,
    };
}
