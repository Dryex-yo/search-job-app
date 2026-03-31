import { ref, computed } from 'vue';

// Shared state across all components
const isDarkModeRef = ref(false);
let initialized = false;

// Apply theme to document
function applyTheme(dark) {
    const html = document.documentElement;
    if (dark) {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
}

// Initialize dark mode from localStorage only once
function initializeDarkMode() {
    if (initialized) return;
    initialized = true;

    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    let shouldBeDark = false;
    if (stored === 'dark') {
        shouldBeDark = true;
    } else if (stored === 'light') {
        shouldBeDark = false;
    } else {
        shouldBeDark = prefersDark;
    }

    isDarkModeRef.value = shouldBeDark;
    applyTheme(shouldBeDark);
    console.log('[Dark Mode] Initialized:', shouldBeDark);
}

// Toggle dark mode
function toggleDarkMode() {
    const newValue = !isDarkModeRef.value;
    isDarkModeRef.value = newValue;
    applyTheme(newValue);
    console.log('[Dark Mode] Toggled to:', newValue);
}

// Set specific theme
function setDarkMode(dark) {
    isDarkModeRef.value = dark;
    applyTheme(dark);
    console.log('[Dark Mode] Set to:', dark);
}

export function useDarkMode() {
    // Watch for changes in other tabs
    if (typeof window !== 'undefined') {
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                isDarkModeRef.value = e.newValue === 'dark';
                applyTheme(isDarkModeRef.value);
                console.log('[Dark Mode] Storage changed:', isDarkModeRef.value);
            }
        });
    }

    return {
        isDarkMode: computed(() => isDarkModeRef.value),
        toggleDarkMode,
        setDarkMode,
        initializeDarkMode,
    };
}
