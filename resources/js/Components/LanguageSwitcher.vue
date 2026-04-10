<script setup>
import { useLanguage } from '@/Composables/useLanguage';
import { useI18n } from 'vue-i18n';

const { currentLanguage, availableLanguages, setLanguage, getCurrentLanguage } = useLanguage();
const { t } = useI18n();

const handleLanguageChange = (languageCode) => {
    setLanguage(languageCode);
};
</script>

<template>
    <div class="language-switcher">
        <!-- Mobile: Simple Flag Selector -->
        <div class="flex sm:hidden">
            <div class="relative group">
                <button 
                    class="flex items-center justify-center w-10 h-10 rounded-lg backdrop-blur-xl border border-white/10 hover:bg-white/5 text-white transition-all duration-200 hover:border-white/20"
                    :title="t('common.language') || 'Language'"
                >
                    <span class="text-lg">{{ getCurrentLanguage?.flag }}</span>
                </button>

                <!-- Mobile Dropdown Menu -->
                <div class="absolute right-0 mt-2 w-44 bg-slate-900/95 border border-white/10 rounded-lg shadow-xl backdrop-blur-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <div class="p-1.5">
                        <button
                            v-for="lang in availableLanguages"
                            :key="lang.code"
                            @click="handleLanguageChange(lang.code)"
                            :class="[
                                'w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-left transition-all duration-150 text-sm',
                                currentLanguage.value === lang.code 
                                    ? 'bg-gradient-to-r from-cyan-500/30 to-blue-500/20 text-cyan-300 border border-cyan-500/40 shadow-lg shadow-cyan-500/10' 
                                    : 'text-slate-300 hover:bg-white/5 hover:text-white'
                            ]"
                        >
                            <span class="text-base">{{ lang.flag }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ lang.name }}</div>
                            </div>
                            <svg 
                                v-if="currentLanguage.value === lang.code"
                                class="w-4 h-4 flex-shrink-0 text-cyan-400"
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

        <!-- Desktop: Detailed Selector -->
        <div class="hidden sm:flex">
            <div class="relative group">
                <!-- Button -->
                <button 
                    class="flex items-center gap-2 px-3 py-2 rounded-lg backdrop-blur-xl border border-white/10 hover:bg-white/5 text-white transition-all duration-200 hover:border-cyan-500/30 hover:bg-cyan-500/5"
                    :title="t('common.language') || 'Language'"
                >
                    <span class="text-lg transition-transform duration-300">{{ getCurrentLanguage?.flag }}</span>
                    <span class="text-sm font-semibold hidden md:inline text-white">{{ currentLanguage.value?.toUpperCase() }}</span>
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5 5-5z"></path>
                    </svg>
                </button>

                <!-- Desktop Dropdown Menu -->
                <div class="absolute right-0 mt-2 w-52 bg-gradient-to-b from-slate-900/98 to-slate-800/95 border border-white/10 rounded-lg shadow-xl backdrop-blur-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <div class="p-2">
                        <button
                            v-for="lang in availableLanguages"
                            :key="lang.code"
                            @click="handleLanguageChange(lang.code)"
                            :class="[
                                'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-150',
                                currentLanguage.value === lang.code 
                                    ? 'bg-gradient-to-r from-cyan-500/25 to-blue-500/15 text-cyan-300 border border-cyan-500/40 shadow-lg shadow-cyan-500/10' 
                                    : 'text-slate-300 hover:bg-white/5 hover:text-white hover:border-white/20'
                            ]"
                        >
                            <span class="text-2xl transition-transform duration-200 group-hover:scale-110">{{ lang.flag }}</span>
                            <div class="flex-1">
                                <div class="font-semibold text-white">{{ lang.name }}</div>
                                <div class="text-xs text-slate-400">{{ lang.code.toUpperCase() }}</div>
                            </div>
                            <transition name="fade">
                                <svg 
                                    v-if="currentLanguage.value === lang.code"
                                    class="w-5 h-5 text-cyan-400 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </transition>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.language-switcher {
    display: inline-block;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
