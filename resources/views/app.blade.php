<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Dark Mode Script (runs before page render to prevent flash) -->
        <script>
            (function() {
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                let isDark = false;
                if (stored === 'dark') {
                    isDark = true;
                } else if (stored === 'light') {
                    isDark = false;
                } else {
                    isDark = prefersDark;
                }
                
                if (isDark) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-gradient-light dark:bg-deep-blue text-light-gray-text dark:text-gray-100 transition-all duration-500 overflow-x-hidden">
        @inertia
    </body>
</html>
