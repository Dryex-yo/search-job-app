<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="x-ua-compatible" content="ie=edge">

        <title inertia>{{ config('app.name') }}</title>

        <!-- DNS Prefetch & Preconnect for faster resource loading -->
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        
        <!-- Fonts with optimal configuration -->
        <link 
            href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" 
            rel="stylesheet"
            media="print"
            onload="this.media='all'"
            integrity="sha384-" 
        />
        <noscript>
            <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        </noscript>

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

        <!-- Performance & SEO Meta Tags -->
        <meta name="description" content="Search Job App - Find your perfect job opportunity">
        <meta name="theme-color" content="#3B82F6">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <!-- Resource hints for performance -->
        <link rel="prefetch" href="{{ route('login') }}" as="fetch">
        <link rel="prefetch" href="{{ route('register') }}" as="fetch">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-gradient-light dark:bg-deep-blue text-light-gray-text dark:text-gray-100 transition-all duration-500 overflow-x-hidden">
        @inertia

        <!-- Performance monitoring (optional - only in production) -->
        @if(config('app.env') === 'production')
        <script>
            // Monitor Core Web Vitals
            window.webVitalsData = {
                lcp: 0,
                fid: 0,
                cls: 0
            };

            // Largest Contentful Paint
            if ('PerformanceObserver' in window) {
                try {
                    const observer = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        const lastEntry = entries[entries.length - 1];
                        window.webVitalsData.lcp = lastEntry.renderTime || lastEntry.loadTime;
                    });
                    observer.observe({ type: 'largest-contentful-paint', buffered: true });
                } catch (e) {
                    // PerformanceObserver not supported
                }
            }
        </script>
        @endif
    </body>
</html>
