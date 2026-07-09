<div class="grid gap-5 md:grid-cols-2">
    @forelse ($stories as $story)
        <a href="{{ route('stories.show', $story) }}" class="group flex overflow-hidden rounded-lg border border-zinc-200 bg-white transition hover:-translate-y-0.5 hover:border-zinc-400 hover:shadow-md hover:shadow-zinc-200/70 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600 dark:hover:shadow-none">
            <div class="story-cover relative hidden aspect-3/4 w-48 shrink-0 overflow-hidden md:block">
                @if ($story->cover_path)
                    <img src="{{ str($story->cover_path)->startsWith(['http://', 'https://', '/']) ? $story->cover_path : asset($story->cover_path) }}" alt="" class="absolute inset-0 size-full object-bottom transition duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-linear-to-t from-zinc-950/80 via-zinc-950/25 to-transparent"></div>
                @else
                    <div class="absolute bottom-4 left-4 z-10 text-3xl font-semibold text-white/95">{{ str($story->title)->substr(0, 1) }}</div>
                @endif
                <span class="absolute bottom-4 z-10 rounded-sm bg-white/15 px-2 py-1 text-xs font-medium text-white backdrop-blur {{ $story->cover_path ? 'left-4' : 'right-4' }}">{{ str($story->status)->headline() }}</span>
            </div>
            <div class="flex min-w-0 flex-1 flex-col p-6">
                <div class="flex items-start justify-between gap-3 md:block">
                    <h2 class="text-xl font-semibold text-zinc-950 group-hover:underline dark:text-zinc-50">{{ $story->title }}</h2>
                    <span class="shrink-0 rounded-sm bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 md:hidden">{{ str($story->status)->headline() }}</span>
                </div>
                <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $story->description }}</p>
                <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-zinc-500 dark:text-zinc-400">
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
