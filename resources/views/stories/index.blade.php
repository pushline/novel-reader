<x-layouts::public :title="__('Library')">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-8">
        <header class="reader-hero rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900 md:p-8">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-end">
                <div>
                    <p class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                        <flux:icon.sparkles variant="micro" />
                        {{ __('Novel Reader') }}
                    </p>
                    <h1 class="mt-3 max-w-3xl text-4xl font-semibold text-zinc-950 dark:text-zinc-50">{{ __('A quieter shelf for very long stories.') }}</h1>
                    <p class="mt-4 max-w-2xl text-zinc-600 dark:text-zinc-300">{{ __('Search, filter, and step directly into chapters without the clutter that usually gathers around web fiction.') }}</p>
                </div>
                <div class="grid gap-3 rounded-lg border border-zinc-200 bg-stone-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Available stories') }}</span>
                        <span class="text-2xl font-semibold text-zinc-950 dark:text-zinc-50">{{ number_format($stories->total()) }}</span>
                    </div>
                    <div class="h-px bg-zinc-200 dark:bg-zinc-800"></div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($genres->take(5) as $genre)
                            <a href="{{ route('home', ['genre' => $genre->slug]) }}" class="rounded-sm border border-zinc-200 px-2 py-1 text-xs text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500">{{ $genre->name }}</a>
                        @endforeach
                    </div>
                    @auth
                        <a href="{{ route('library') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-950">
                            <flux:icon.bookmark variant="micro" />
                            {{ __('My library') }}
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <form method="GET" class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm shadow-zinc-200/40 dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-none md:grid-cols-[1fr_180px_180px_auto]">
            <div class="relative">
                <flux:icon.magnifying-glass variant="micro" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" />
                <input name="search" value="{{ request('search') }}" placeholder="{{ __('Search stories') }}" class="h-10 w-full rounded-md border border-zinc-300 bg-white pl-9 pr-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
            </div>
            <select name="status" class="h-10 rounded-md border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (['ongoing', 'completed', 'hiatus'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->headline() }}</option>
                @endforeach
            </select>
            <select name="genre" class="h-10 rounded-md border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <option value="">{{ __('All genres') }}</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre->slug }}" @selected(request('genre') === $genre->slug)>{{ $genre->name }}</option>
                @endforeach
            </select>
            <button class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-950">
                <flux:icon.funnel variant="micro" />
                {{ __('Filter') }}
            </button>
        </form>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($stories as $story)
                <a href="{{ route('stories.show', $story) }}" class="group flex overflow-hidden rounded-lg border border-zinc-200 bg-white transition hover:-translate-y-0.5 hover:border-zinc-400 hover:shadow-md hover:shadow-zinc-200/70 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600 dark:hover:shadow-none">
                    <div class="story-cover relative hidden aspect-3/4 w-36 shrink-0 overflow-hidden md:block">
                        @if ($story->cover_path)
                            <img src="{{ str($story->cover_path)->startsWith(['http://', 'https://', '/']) ? $story->cover_path : asset($story->cover_path) }}" alt="" class="absolute inset-0 size-full object-cover object-bottom transition duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-linear-to-t from-zinc-950/80 via-zinc-950/25 to-transparent"></div>
                        @else
                            <div class="absolute bottom-4 left-4 z-10 text-3xl font-semibold text-white/95">{{ str($story->title)->substr(0, 1) }}</div>
                        @endif
                        <span class="absolute bottom-4 z-10 rounded-sm bg-white/15 px-2 py-1 text-xs font-medium text-white backdrop-blur {{ $story->cover_path ? 'left-4' : 'right-4' }}">{{ str($story->status)->headline() }}</span>
                    </div>
                    <div class="flex min-w-0 flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-3 md:block">
                            <h2 class="text-lg font-semibold text-zinc-950 group-hover:underline dark:text-zinc-50">{{ $story->title }}</h2>
                            <span class="shrink-0 rounded-sm bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 md:hidden">{{ str($story->status)->headline() }}</span>
                        </div>
                        <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $story->description }}</p>
                        <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="inline-flex items-center gap-1">
                                <flux:icon.document-text variant="micro" />
                                {{ trans_choice(':count chapter|:count chapters', $story->chapters_count) }}
                            </span>
                            @foreach ($story->genres->take(3) as $genre)
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon.tag variant="micro" />
                                    {{ $genre->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center text-zinc-500 dark:border-zinc-700 dark:text-zinc-400 md:col-span-2 xl:col-span-3">
                    {{ __('No stories match the current filters.') }}
                </div>
            @endforelse
        </div>

        {{ $stories->links() }}
    </div>
</x-layouts::public>
