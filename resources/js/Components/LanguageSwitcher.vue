<script setup>
import { useI18n } from 'vue-i18n';
import { ref, computed, watch, onClickOutside } from 'vue';

const { locale, t } = useI18n();
const isLoading = ref(false);
const error = ref(null);
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

const languages = [
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'id', name: 'Bahasa Indonesia', flag: '🇮🇩' }
];

// Reactive getter for current language
const currentLanguage = computed(() => {
    return languages.find(lang => lang.code === locale.value) || languages[0];
});

const changeLanguage = async (code) => {
    // Prevent duplicate calls
    if (locale.value === code || isLoading.value) return;
    
    const previousLocale = locale.value;
    isLoading.value = true;
    error.value = null;
    isDropdownOpen.value = false;

    try {
        // Update frontend locale immediately
        locale.value = code;
        
        // Persist to localStorage and sessionStorage
        localStorage.setItem('app_locale', code);
        sessionStorage.setItem('app_locale', code);
        
        // Update document attributes for accessibility
        document.documentElement.lang = code;
        document.documentElement.setAttribute('data-locale', code);

        // Send to backend using fetch to set cookie
        const response = await fetch(`/locale/${code}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error('Backend failed to set locale');
        }

        // Success: Reload current page with new locale to update all components
        // This ensures Inertia props and all child components get the new locale
        setTimeout(() => {
            window.location.reload();
        }, 300);

    } catch (err) {
        console.error('Failed to set locale:', err);
        error.value = 'Failed to change language. Please try again.';
        
        // Revert changes on error
        locale.value = previousLocale;
        localStorage.setItem('app_locale', previousLocale);
        sessionStorage.setItem('app_locale', previousLocale);
        document.documentElement.lang = previousLocale;
        document.documentElement.setAttribute('data-locale', previousLocale);
    } finally {
        isLoading.value = false;
    }
};

// Clear error message after 3 seconds
watch(error, (newError) => {
    if (newError) {
        setTimeout(() => {
            error.value = null;
        }, 3000);
    }
});

// Close dropdown when clicking outside
onClickOutside(dropdownRef, () => {
    isDropdownOpen.value = false;
});
</script>

<template>
    <div class="language-switcher" ref="dropdownRef">
        <!-- Mobile: Simple Flag Selector -->
        <div class="flex sm:hidden">
            <div class="relative">
                <button 
                    @click.stop="isDropdownOpen = !isDropdownOpen"
                    class="flex items-center justify-center w-10 h-10 rounded-lg backdrop-blur-xl border border-white/10 hover:bg-white/5 text-white transition-all duration-200 hover:border-white/20"
                    :title="t('common.language') || 'Language'"
                    :disabled="isLoading"
                    :class="isDropdownOpen && 'bg-cyan-500/10 border-cyan-500/30'"
                >
                    <span class="text-lg">{{ currentLanguage.flag }}</span>
                </button>

                <!-- Mobile Dropdown Menu -->
                <transition
                    enter-active-class="transition ease-out duration-150"
                    enter-from-class="opacity-0 scale-95 -translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition ease-in duration-100"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 -translate-y-2"
                >
                    <div 
                        v-show="isDropdownOpen"
                        class="absolute right-0 top-full mt-2 min-w-max max-w-xs bg-slate-900/95 border border-white/10 rounded-lg shadow-xl backdrop-blur-xl z-50"
                    >
                        <div class="p-1.5">
                            <button
                                v-for="lang in languages"
                                :key="lang.code"
                                @click="changeLanguage(lang.code)"
                                :class="[
                                    'w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-left transition-all duration-150 text-sm',
                                    locale === lang.code
                                        ? 'bg-gradient-to-r from-cyan-500/30 to-blue-500/20 text-cyan-300 border border-cyan-500/40 shadow-lg shadow-cyan-500/10' 
                                        : 'text-slate-300 hover:bg-white/5 hover:text-white'
                                ]"
                            >
                                <span class="text-base flex-shrink-0">{{ lang.flag }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium truncate">{{ lang.name }}</div>
                                </div>
                                <svg 
                                    v-if="locale === lang.code"
                                    class="w-4 h-4 flex-shrink-0 text-cyan-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Desktop: Detailed Selector -->
        <div class="hidden sm:flex">
            <div class="relative">
                <!-- Button -->
                <button 
                    @click.stop="isDropdownOpen = !isDropdownOpen"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg backdrop-blur-xl border border-white/10 hover:bg-white/5 text-white transition-all duration-200 hover:border-cyan-500/30 hover:bg-cyan-500/5"
                    :title="t('common.language') || 'Language'"
                    :disabled="isLoading"
                    :class="isDropdownOpen && 'bg-cyan-500/10 border-cyan-500/30'"
                >
                    <span class="text-lg transition-transform duration-300">{{ currentLanguage.flag }}</span>
                    <span class="text-sm font-semibold hidden md:inline text-white">{{ locale.toUpperCase() }}</span>
                    <svg 
                        class="w-4 h-4 transition-transform duration-300 text-slate-400" 
                        :class="isDropdownOpen && 'rotate-180'"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5 5-5z"></path>
                    </svg>
                </button>

                <!-- Desktop Dropdown Menu -->
                <transition
                    enter-active-class="transition ease-out duration-150"
                    enter-from-class="opacity-0 scale-95 -translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition ease-in duration-100"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 -translate-y-2"
                >
                    <div 
                        v-show="isDropdownOpen"
                        class="absolute right-0 top-full mt-2 min-w-max bg-gradient-to-b from-slate-900/98 to-slate-800/95 border border-white/10 rounded-lg shadow-xl backdrop-blur-xl z-50"
                    >
                        <div class="p-2">
                            <button
                                v-for="lang in languages"
                                :key="lang.code"
                                @click="changeLanguage(lang.code)"
                                :class="[
                                    'flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-150 whitespace-nowrap',
                                    locale === lang.code
                                        ? 'bg-gradient-to-r from-cyan-500/25 to-blue-500/15 text-cyan-300 border border-cyan-500/40 shadow-lg shadow-cyan-500/10' 
                                        : 'text-slate-300 hover:bg-white/5 hover:text-white hover:border-white/20'
                                ]"
                            >
                                <span class="text-2xl flex-shrink-0">{{ lang.flag }}</span>
                                <div class="flex-1">
                                    <div class="font-semibold text-white">{{ lang.name }}</div>
                                    <div class="text-xs text-slate-400">{{ lang.code.toUpperCase() }}</div>
                                </div>
                                <svg 
                                    v-if="locale === lang.code"
                                    class="w-5 h-5 text-cyan-400 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<style scoped>
.language-switcher {
    display: inline-block;
}

button {
    will-change: transform, background-color;
}
</style>
