@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>
            {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
        </title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <meta name="description" content="A dark, focused home for long-form stories.">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Novel Reader') : config('app.name', 'Novel Reader') }}">
        <meta property="og:description" content="A dark, focused home for long-form stories.">
        <meta property="og:image" content="{{ url('/brand/novel-reader-social.png') }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Novel Reader') : config('app.name', 'Novel Reader') }}">
        <meta name="twitter:description" content="A dark, focused home for long-form stories.">
        <meta name="twitter:image" content="{{ url('/brand/novel-reader-social.png') }}">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            const publicTheme = localStorage.getItem('reader-public-theme') || 'dark';
            document.documentElement.classList.toggle('dark', publicTheme === 'dark');
        </script>
    </head>
    <body class="min-h-screen bg-stone-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="sticky top-0 z-30 border-b border-zinc-200 bg-stone-50/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-950 dark:text-zinc-50">
                    <span class="inline-flex size-9 items-center justify-center overflow-hidden rounded-md bg-zinc-950 text-white ring-1 ring-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:ring-zinc-700">
                        <x-app-logo-icon class="size-8" />
                    </span>
                    {{ config('app.name', 'Novel Reader') }}
                </a>
                <nav class="flex items-center gap-1 text-sm text-zinc-600 dark:text-zinc-300 sm:gap-3">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 hover:text-zinc-950 dark:hover:text-white">
                        <flux:icon.book-open variant="micro" />
                        <span class="max-sm:hidden">{{ __('Library') }}</span>
                    </a>
                    <button id="public-theme-toggle" type="button" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 hover:text-zinc-950 dark:hover:text-white">
                        <flux:icon.sun variant="micro" class="hidden dark:inline-block" />
                        <flux:icon.moon variant="micro" class="dark:hidden" />
                        <span id="public-theme-label" class="max-sm:hidden">{{ __('Light') }}</span>
                    </button>
                    @auth
                        <a href="{{ route('library') }}" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 hover:text-zinc-950 dark:hover:text-white">
                            <flux:icon.bookmark variant="micro" />
                            <span class="max-sm:hidden">{{ __('My library') }}</span>
                        </a>
                        <a href="{{ route('settings') }}" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 hover:text-zinc-950 dark:hover:text-white">
                            <flux:icon.cog-6-tooth variant="micro" />
                            <span class="max-sm:hidden">{{ __('Settings') }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 hover:text-zinc-950 dark:hover:text-white">
                            <flux:icon.arrow-right-start-on-rectangle variant="micro" />
                            <span class="max-sm:hidden">{{ __('Log in') }}</span>
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 rounded-md bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-950">
                            <flux:icon.user-plus variant="micro" />
                            {{ __('Register') }}
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="px-4 py-8 md:py-10">
            {{ $slot }}
        </main>

        @fluxScripts
        <script>
            (() => {
                const button = document.getElementById('public-theme-toggle');
                const label = document.getElementById('public-theme-label');
                const apply = (theme) => {
                    document.documentElement.classList.toggle('dark', theme === 'dark');
                    label.textContent = theme === 'dark' ? 'Light' : 'Dark';
                    localStorage.setItem('reader-public-theme', theme);
                };

                apply(localStorage.getItem('reader-public-theme') || 'dark');
                button.addEventListener('click', () => {
                    apply(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
                });
            })();
        </script>
    </body>
</html>
