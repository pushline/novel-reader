@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        <div class="flex flex-1 items-center gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-10 flex-1 items-center justify-center gap-1.5 rounded-md border border-zinc-200 px-4 text-sm font-medium text-zinc-400 dark:border-zinc-800 dark:text-zinc-600">
                    <flux:icon.chevron-left variant="micro" />
                    {{ __('Previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-10 flex-1 items-center justify-center gap-1.5 rounded-md border border-zinc-300 px-4 text-sm font-medium text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">
                    <flux:icon.chevron-left variant="micro" />
                    {{ __('Previous') }}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-10 flex-1 items-center justify-center gap-1.5 rounded-md border border-zinc-300 px-4 text-sm font-medium text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">
                    {{ __('Next') }}
                    <flux:icon.chevron-right variant="micro" />
                </a>
            @else
                <span class="inline-flex h-10 flex-1 items-center justify-center gap-1.5 rounded-md border border-zinc-200 px-4 text-sm font-medium text-zinc-400 dark:border-zinc-800 dark:text-zinc-600">
                    {{ __('Next') }}
                    <flux:icon.chevron-right variant="micro" />
                </span>
            @endif
        </div>

        <p class="hidden text-sm text-zinc-500 sm:block dark:text-zinc-400">
            {{ __('Showing') }}
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $paginator->firstItem() ?? 0 }}</span>
            {{ __('to') }}
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $paginator->lastItem() ?? 0 }}</span>
            {{ __('of') }}
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ number_format($paginator->total()) }}</span>
        </p>

        <div class="hidden items-center gap-1 sm:flex">
            @if ($paginator->onFirstPage())
                <span class="inline-flex size-9 items-center justify-center rounded-md border border-zinc-200 text-zinc-300 dark:border-zinc-800 dark:text-zinc-700">
                    <flux:icon.chevron-left variant="micro" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}" class="inline-flex size-9 items-center justify-center rounded-md border border-zinc-300 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    <flux:icon.chevron-left variant="micro" />
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex size-9 items-center justify-center text-sm text-zinc-400 dark:text-zinc-600">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex size-9 items-center justify-center rounded-md bg-zinc-950 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-950">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="inline-flex size-9 items-center justify-center rounded-md text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}" class="inline-flex size-9 items-center justify-center rounded-md border border-zinc-300 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    <flux:icon.chevron-right variant="micro" />
                </a>
            @else
                <span class="inline-flex size-9 items-center justify-center rounded-md border border-zinc-200 text-zinc-300 dark:border-zinc-800 dark:text-zinc-700">
                    <flux:icon.chevron-right variant="micro" />
                </span>
            @endif
        </div>
    </nav>
@endif
