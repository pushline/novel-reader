<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="mb-2 inline-flex items-center justify-center gap-2 font-semibold text-zinc-950 dark:text-zinc-50" wire:navigate>
                    <span class="inline-flex size-9 items-center justify-center overflow-hidden rounded-md bg-zinc-950 text-white ring-1 ring-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:ring-zinc-700">
                        <x-app-logo-icon class="size-8" />
                    </span>
                    <span>{{ config('app.name', 'Novel Reader') }}</span>
                </a>
                <div class="flex flex-col gap-6">
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
