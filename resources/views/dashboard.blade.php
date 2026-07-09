<x-layouts::app :title="__('Dashboard')">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-8">
        <section class="reader-hero overflow-hidden rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900 md:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                        <flux:icon.sparkles variant="micro" />
                        {{ __('Reading desk') }}
                    </p>
                    <h1 class="mt-3 text-3xl font-semibold text-zinc-950 dark:text-zinc-50">{{ __('Pick up where the page left you.') }}</h1>
                    <p class="mt-3 text-zinc-600 dark:text-zinc-300">{{ __('Your saved chapters, bookmarks, and latest imports are gathered into one focused workspace.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('home') }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-white">
                        <flux:icon.magnifying-glass variant="micro" />
                        {{ __('Browse stories') }}
                    </a>
                    <a href="{{ route('library') }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-zinc-300 px-4 text-sm font-medium text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">
                        <flux:icon.bookmark variant="micro" />
                        {{ __('Open library') }}
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="inline-flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400"><flux:icon.book-open variant="micro" /> {{ __('Stories') }}</p>
                <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-zinc-50">{{ number_format($storyCount) }}</div>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="inline-flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400"><flux:icon.document-text variant="micro" /> {{ __('Chapters') }}</p>
                <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-zinc-50">{{ number_format($chapterCount) }}</div>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="inline-flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400"><flux:icon.bookmark variant="micro" /> {{ __('Bookmarks') }}</p>
                <div class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-zinc-50">{{ number_format($bookmarkCount) }}</div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
            <section>
                <div class="mb-3 flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-zinc-50">{{ __('Continue reading') }}</h2>
                    <a href="{{ route('library') }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">{{ __('View all') }}</a>
                </div>
                <div class="divide-y divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
                    @forelse ($progressItems as $item)
                        <a href="{{ route('chapters.show', [$item->story, $item->chapter->number]) }}" class="group grid gap-3 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800 sm:grid-cols-[1fr_auto] sm:items-center">
                            <div class="min-w-0">
                                <div class="font-medium text-zinc-950 dark:text-zinc-50">{{ $item->story->title }}</div>
                                <div class="mt-1 truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $item->chapter->number }}. {{ $item->chapter->title }}</div>
                                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $item->scroll_percent }}%"></div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-2 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                                {{ $item->scroll_percent }}%
                                <flux:icon.arrow-right variant="micro" class="transition group-hover:translate-x-0.5" />
                            </span>
                        </a>
                    @empty
                        <div class="p-6 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Start a chapter and your progress will appear here.') }}</div>
                    @endforelse
                </div>
            </section>

            <section>
                <div class="mb-3 flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-zinc-50">{{ __('Fresh on the shelf') }}</h2>
                    <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">{{ __('Browse') }}</a>
                </div>
                <div class="grid gap-3">
                    @foreach ($freshStories as $story)
                        <a href="{{ route('stories.show', $story) }}" class="group rounded-lg border border-zinc-200 bg-white p-4 hover:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-medium text-zinc-950 group-hover:underline dark:text-zinc-50">{{ $story->title }}</h3>
                                <span class="rounded-sm bg-sky-50 px-2 py-1 text-xs text-sky-700 dark:bg-sky-950 dark:text-sky-300">{{ $story->chapters_count }}</span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($story->genres->take(3) as $genre)
                                    <span class="rounded-sm border border-zinc-200 px-2 py-0.5 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">{{ $genre->name }}</span>
                                @endforeach
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
