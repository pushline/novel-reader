<x-layouts::app :title="__('My library')">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-8">
        <header class="reader-hero rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="inline-flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                        <flux:icon.bookmark variant="micro" />
                        {{ __('Personal shelf') }}
                    </p>
                    <h1 class="mt-3 text-3xl font-semibold text-zinc-950 dark:text-zinc-50">{{ __('My library') }}</h1>
                    <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-300">{{ __('Progress and bookmarked chapters live here, ready for quick return trips.') }}</p>
                </div>
                <a href="{{ route('home') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-zinc-300 px-4 text-sm font-medium text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">
                    <flux:icon.magnifying-glass variant="micro" />
                    {{ __('Find more stories') }}
                </a>
            </div>
        </header>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">
            <section>
                <h2 class="inline-flex items-center gap-2 text-xl font-semibold text-zinc-950 dark:text-zinc-50">
                    <flux:icon.book-open variant="mini" class="text-zinc-400 dark:text-zinc-500" />
                    {{ __('Continue reading') }}
                </h2>
                <div class="mt-4 divide-y divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
                @forelse ($progressItems as $item)
                    <a href="{{ route('chapters.show', [$item->story, $item->chapter->number]) }}" class="group grid gap-3 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800 sm:grid-cols-[1fr_auto] sm:items-center">
                        <div class="min-w-0">
                            <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $item->story->title }}</div>
                            <div class="mt-1 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                <span class="truncate">{{ $item->chapter->number }}. {{ $item->chapter->title }}</span>
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                    <flux:icon.clock variant="micro" />
                                    {{ $item->scroll_percent }}%
                                </span>
                            </div>
                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $item->scroll_percent }}%"></div>
                            </div>
                        </div>
                        <flux:icon.chevron-right variant="micro" class="shrink-0 text-zinc-400 transition group-hover:translate-x-0.5 dark:text-zinc-500" />
                    </a>
                @empty
                    <div class="p-6 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No reading progress saved yet.') }}</div>
                @endforelse
                </div>
            </section>

            <section>
                <h2 class="inline-flex items-center gap-2 text-xl font-semibold text-zinc-950 dark:text-zinc-50">
                    <flux:icon.bookmark variant="mini" class="text-zinc-400 dark:text-zinc-500" />
                    {{ __('Bookmarks') }}
                </h2>
                <div class="mt-4 grid gap-3">
                    @forelse ($bookmarks as $bookmark)
                        <a href="{{ route('chapters.show', [$bookmark->chapter->story, $bookmark->chapter->number]) }}" class="group rounded-lg border border-zinc-200 bg-white p-4 hover:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $bookmark->chapter->story->title }}</div>
                                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $bookmark->chapter->number }}. {{ $bookmark->chapter->title }}</div>
                                </div>
                                <flux:icon.chevron-right variant="micro" class="mt-1 shrink-0 text-zinc-400 transition group-hover:translate-x-0.5 dark:text-zinc-500" />
                            </div>
                            @if ($bookmark->note)
                                <p class="mt-3 rounded-md bg-stone-50 p-3 text-sm leading-6 text-zinc-600 dark:bg-zinc-950 dark:text-zinc-300">{{ $bookmark->note }}</p>
                            @endif
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">{{ __('No bookmarks yet.') }}</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
