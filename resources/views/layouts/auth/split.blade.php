<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-neutral-900"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                    <span class="me-2 inline-flex size-10 items-center justify-center overflow-hidden rounded-md bg-zinc-100 text-zinc-950 ring-1 ring-white/10">
                        <x-app-logo-icon class="size-9" />
                    </span>
                    {{ config('app.name', 'Novel Reader') }}
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-87.5">
                    <a href="{{ route('home') }}" class="z-20 inline-flex items-center justify-center gap-2 font-semibold text-zinc-950 dark:text-zinc-50 lg:hidden" wire:navigate>
                        <span class="inline-flex size-9 items-center justify-center overflow-hidden rounded-md bg-zinc-950 text-white ring-1 ring-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:ring-zinc-700">
                            <x-app-logo-icon class="size-8" />
                        </span>
                        <span>{{ config('app.name', 'Novel Reader') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
