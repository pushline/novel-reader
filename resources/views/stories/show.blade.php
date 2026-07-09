<x-layouts::public :title="$story->title">
    <div class="mx-auto grid w-full max-w-7xl gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
        <section class="flex flex-col gap-6">
            <div class="reader-hero rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900 md:p-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                    <flux:icon.arrow-left variant="micro" />
                    {{ __('Library') }}
                </a>
                <div class="mt-6 grid justify-items-center gap-6 text-center md:grid-cols-[256px_1fr] md:items-start md:justify-items-start md:text-left lg:items-stretch">
                    @if ($story->cover_path)
                        <div class="relative aspect-3/4 w-56 overflow-hidden rounded-lg bg-zinc-100 shadow-sm dark:bg-zinc-800 md:w-64">
                            <img src="{{ str($story->cover_path)->startsWith(['http://', 'https://', '/']) ? $story->cover_path : asset($story->cover_path) }}" alt="" class="absolute inset-0 size-full object-cover object-bottom">
                        </div>
                    @else
                        <div class="story-cover flex aspect-3/4 w-56 items-end rounded-lg p-4 text-3xl font-semibold text-white shadow-sm md:w-64">
                            {{ str($story->title)->substr(0, 1) }}
                        </div>
                    @endif
                    <div class="flex flex-col items-center md:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap justify-center gap-2 md:justify-start">
                                <span class="rounded-sm bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">{{ str($story->status)->headline() }}</span>
                                @foreach ($story->genres as $genre)
                                    <span class="rounded-sm border border-zinc-200 px-2 py-1 text-xs text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ $genre->name }}</span>
                                @endforeach
                            </div>
                            <h1 class="mt-3 text-3xl font-semibold text-zinc-950 dark:text-zinc-50">{{ $story->title }}</h1>
                            <p class="mt-4 max-w-3xl leading-7 text-zinc-600 dark:text-zinc-300">{{ $story->description }}</p>
                        </div>
                        <div class="mt-6 flex flex-wrap justify-center gap-2 md:justify-start">
                            @if ($progress?->chapter)
                                <a href="{{ route('chapters.show', [$story, $progress->chapter->number]) }}" class="group inline-flex h-10 items-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-medium text-white shadow-sm shadow-zinc-950/15 transition duration-150 ease-out hover:-translate-y-px hover:bg-zinc-800 hover:shadow-md hover:shadow-zinc-950/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 active:translate-y-0 active:bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-950 dark:shadow-black/30 dark:hover:bg-white dark:hover:shadow-black/40 dark:focus-visible:outline-zinc-300 dark:active:bg-zinc-200">
                                    <flux:icon.play variant="micro" class="transition-transform duration-150 group-hover:scale-110" />
                                    {{ __('Continue') }} {{ $progress->scroll_percent }}%
                                </a>
                            @elseif ($firstChapter)
                                <a href="{{ route('chapters.show', [$story, $firstChapter->number]) }}" class="group inline-flex h-10 items-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-medium text-white shadow-sm shadow-zinc-950/15 transition duration-150 ease-out hover:-translate-y-px hover:bg-zinc-800 hover:shadow-md hover:shadow-zinc-950/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 active:translate-y-0 active:bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-950 dark:shadow-black/30 dark:hover:bg-white dark:hover:shadow-black/40 dark:focus-visible:outline-zinc-300 dark:active:bg-zinc-200">
                                    <flux:icon.play variant="micro" class="transition-transform duration-150 group-hover:scale-110" />
                                    {{ __('Start reading') }}
                                </a>
                            @endif
                            <a href="#chapters" class="inline-flex h-10 items-center gap-2 rounded-md border border-zinc-300 px-4 text-sm font-medium text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">
                                <flux:icon.list-bullet variant="micro" />
                                {{ __('Chapter list') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="chapters" class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4 border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
                    <h2 class="font-semibold text-zinc-950 dark:text-zinc-50">{{ __('Chapters') }}</h2>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ trans_choice(':count entry|:count entries', $chapterCount) }}</span>
                </div>
                @foreach ($chapters as $chapter)
                    <a href="{{ route('chapters.show', [$story, $chapter->number]) }}" class="group flex items-center justify-between gap-4 border-b border-zinc-100 px-4 py-3 text-sm last:border-b-0 hover:bg-stone-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <span class="min-w-0">
                            <span class="mr-3 inline-flex size-8 items-center justify-center rounded-sm bg-zinc-100 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">{{ $chapter->number }}</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $chapter->title }}</span>
                        </span>
                        <span class="flex items-center gap-3">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ number_format($chapter->word_count) }} {{ __('words') }}</span>
                            <flux:icon.chevron-right variant="micro" class="text-zinc-400 transition group-hover:translate-x-0.5 dark:text-zinc-500" />
                        </span>
                    </a>
                @endforeach
            </div>

            @if ($chapters->hasPages())
                <div class="rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                    {{ $chapters->links() }}
                </div>
            @endif
        </section>

        <aside class="h-fit rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 lg:sticky lg:top-24">
            <h2 class="inline-flex items-center gap-2 font-semibold text-zinc-950 dark:text-zinc-50">
                <flux:icon.information-circle variant="mini" class="text-zinc-400 dark:text-zinc-500" />
                {{ __('Details') }}
            </h2>
            <dl class="mt-4 grid gap-4 text-sm">
                <div>
                    <dt class="inline-flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                        <flux:icon.user variant="micro" />
                        {{ __('Authors') }}
                    </dt>
                    <dd class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $story->authors->pluck('name')->join(', ') ?: __('Unknown') }}</dd>
                </div>
                <div>
                    <dt class="inline-flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                        <flux:icon.document-text variant="micro" />
                        {{ __('Chapters') }}
                    </dt>
                    <dd class="mt-1 text-zinc-900 dark:text-zinc-100">{{ number_format($chapterCount) }}</dd>
                </div>
                <div>
                    <dt class="inline-flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                        <flux:icon.bars-3-bottom-left variant="micro" />
                        {{ __('Words') }}
                    </dt>
                    <dd class="mt-1 text-zinc-900 dark:text-zinc-100">{{ number_format($totalWords) }}</dd>
                </div>
                @if ($story->source_url)
                    <div>
                        <dt class="inline-flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.link variant="micro" />
                            {{ __('Source') }}
                        </dt>
                        <dd class="mt-1 wrap-break-word text-zinc-900 dark:text-zinc-100">{{ $story->source_url }}</dd>
                    </div>
                @endif
            </dl>
        </aside>
    </div>
</x-layouts::public>
