import { useI18n } from 'vue-i18n';
import { ref } from 'vue';

export function useLanguage() {
    const { locale, messages } = useI18n();
    const currentLanguage = ref(locale.value);
    const availableLanguages = [
        { code: 'en', name: 'English', flag: '🇺🇸' },
        { code: 'id', name: 'Bahasa Indonesia', flag: '🇮🇩' }
    ];

    const setLanguage = (languageCode) => {
        if (availableLanguages.some(lang => lang.code === languageCode)) {
            locale.value = languageCode;
            currentLanguage.value = languageCode;
            localStorage.setItem('app_locale', languageCode);
            document.documentElement.lang = languageCode;
        }
    };

    const getAvailableLanguages = () => availableLanguages;
    
    const getCurrentLanguage = () => {
        return availableLanguages.find(lang => lang.code === currentLanguage.value);
    };

    return {
        currentLanguage: currentLanguage.value,
        availableLanguages,
        setLanguage,
        getAvailableLanguages,
        getCurrentLanguage,
    };
}
