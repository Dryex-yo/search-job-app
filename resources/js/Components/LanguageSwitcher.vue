<script setup>
import { useLanguage } from '@/Composables/useLanguage';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const { currentLanguage, availableLanguages, setLanguage } = useLanguage();
const { t } = useI18n();

const handleLanguageChange = (languageCode) => {
    setLanguage(languageCode);
};

const currentLang = computed(() => {
    return availableLanguages.find(lang => lang.code === currentLanguage);
});
</script>

<template>
    <div class="language-switcher">
        <!-- Dropdown -->
        <div class="relative group">
            <!-- Button -->
            <button 
                class="flex items-center gap-2 px-3 py-2 rounded-lg backdrop-blur-xl border border-white/10 hover:bg-white/5 text-white transition-colors duration-200 hover:border-white/20"
                :title="t('common.language')"
            >
                <span class="text-lg">
                    {{ currentLang?.flag }}
                </span>
                <span class="text-sm font-medium hidden sm:inline">{{ currentLang?.code.toUpperCase() }}</span>
                <svg class="w-4 h-4 ml-1 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div class="absolute right-0 mt-2 w-48 bg-slate-900/95 border border-white/10 rounded-lg shadow-lg backdrop-blur-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                <div class="p-2">
                    <button
                        v-for="lang in availableLanguages"
                        :key="lang.code"
                        @click="handleLanguageChange(lang.code)"
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-colors duration-200',
                            currentLanguage === lang.code 
                                ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' 
                                : 'text-slate-300 hover:bg-white/5 hover:text-white'
                        ]"
                    >
                        <span class="text-xl">{{ lang.flag }}</span>
                        <div>
                            <div class="font-medium">{{ lang.name }}</div>
                            <div class="text-xs opacity-70">{{ lang.code }}</div>
                        </div>
                        <svg 
                            v-if="currentLanguage === lang.code"
                            class="w-4 h-4 ml-auto text-cyan-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.language-switcher {
    display: inline-block;
}
</style>
