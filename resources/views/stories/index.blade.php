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
                        <span data-library-count class="text-2xl font-semibold text-zinc-950 dark:text-zinc-50">{{ number_format($stories->total()) }}</span>
                    </div>
                    <div class="h-px bg-zinc-200 dark:bg-zinc-800"></div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($genres->take(5) as $genre)
                            <a href="{{ route('home', ['genre' => $genre->slug]) }}" data-library-genre="{{ $genre->slug }}" class="rounded-sm border border-zinc-200 px-2 py-1 text-xs text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500">{{ $genre->name }}</a>
                        @endforeach
                    </div>
                    @auth
                        <a href="{{ route('library') }}" class="group inline-flex h-10 items-center justify-center gap-2 rounded-md bg-zinc-950 px-4 text-sm font-medium text-white shadow-sm shadow-zinc-950/15 transition duration-150 ease-out hover:-translate-y-px hover:bg-zinc-800 hover:shadow-md hover:shadow-zinc-950/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 active:translate-y-0 active:bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-950 dark:shadow-black/30 dark:hover:bg-white dark:hover:shadow-black/40 dark:focus-visible:outline-zinc-300 dark:active:bg-zinc-200">
                            <flux:icon.bookmark variant="micro" class="transition-transform duration-150 group-hover:scale-110" />
                            {{ __('My library') }}
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <form method="GET" action="{{ route('home') }}" data-library-filter-form class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm shadow-zinc-200/40 dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-none md:grid-cols-[1fr_180px_180px_auto]">
            <div class="relative">
                <flux:icon.magnifying-glass variant="micro" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" />
                <input name="search" value="{{ request('search') }}" placeholder="{{ __('Search stories') }}" class="h-10 w-full rounded-md border border-zinc-300 bg-white pl-9 pr-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
            </div>
            <div data-custom-select class="relative">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <button type="button" data-custom-select-button class="group inline-flex h-10 w-full items-center justify-between gap-3 rounded-md border border-zinc-300 bg-white px-3 text-left text-sm text-zinc-900 shadow-sm shadow-zinc-200/40 transition duration-150 ease-out hover:border-zinc-400 hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 active:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:shadow-none dark:hover:border-zinc-600 dark:hover:bg-zinc-900 dark:focus-visible:outline-zinc-300 dark:active:bg-zinc-800" aria-haspopup="listbox" aria-expanded="false">
                    <span data-custom-select-label>{{ request('status') ? str(request('status'))->headline() : __('All statuses') }}</span>
                    <flux:icon.chevron-down variant="micro" class="shrink-0 text-zinc-400 transition duration-150 group-aria-expanded:rotate-180 dark:text-zinc-500" />
                </button>
                <div data-custom-select-menu class="absolute left-0 right-0 top-full z-20 mt-2 hidden overflow-hidden rounded-md border border-zinc-200 bg-white shadow-lg shadow-zinc-950/10 dark:border-zinc-700 dark:bg-zinc-900 dark:shadow-black/40" role="listbox">
                    <button type="button" data-custom-select-option data-value="" class="flex h-9 w-full cursor-pointer items-center px-3 text-left text-sm text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">{{ __('All statuses') }}</button>
                    @foreach (['ongoing', 'completed', 'hiatus'] as $status)
                        <button type="button" data-custom-select-option data-value="{{ $status }}" class="flex h-9 w-full cursor-pointer items-center px-3 text-left text-sm text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">{{ str($status)->headline() }}</button>
                    @endforeach
                </div>
            </div>
            <div data-custom-select class="relative">
                <input type="hidden" name="genre" value="{{ request('genre') }}">
                <button type="button" data-custom-select-button class="group inline-flex h-10 w-full items-center justify-between gap-3 rounded-md border border-zinc-300 bg-white px-3 text-left text-sm text-zinc-900 shadow-sm shadow-zinc-200/40 transition duration-150 ease-out hover:border-zinc-400 hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 active:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:shadow-none dark:hover:border-zinc-600 dark:hover:bg-zinc-900 dark:focus-visible:outline-zinc-300 dark:active:bg-zinc-800" aria-haspopup="listbox" aria-expanded="false">
                    <span data-custom-select-label>{{ $genres->firstWhere('slug', request('genre'))?->name ?? __('All genres') }}</span>
                    <flux:icon.chevron-down variant="micro" class="shrink-0 text-zinc-400 transition duration-150 group-aria-expanded:rotate-180 dark:text-zinc-500" />
                </button>
                <div data-custom-select-menu class="custom-scrollbar absolute left-0 right-0 top-full z-20 mt-2 hidden max-h-64 overflow-auto rounded-md border border-zinc-200 bg-white shadow-lg shadow-zinc-950/10 dark:border-zinc-700 dark:bg-zinc-900 dark:shadow-black/40" role="listbox">
                    <button type="button" data-custom-select-option data-value="" class="flex h-9 w-full cursor-pointer items-center px-3 text-left text-sm text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">{{ __('All genres') }}</button>
                    @foreach ($genres as $genre)
                        <button type="button" data-custom-select-option data-value="{{ $genre->slug }}" class="flex h-9 w-full cursor-pointer items-center px-3 text-left text-sm text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">{{ $genre->name }}</button>
                    @endforeach
                </div>
            </div>
            <button class="group inline-flex h-10 items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 text-sm font-medium text-white shadow-sm shadow-zinc-950/15 transition duration-150 ease-out hover:-translate-y-px hover:bg-zinc-700 hover:shadow-md hover:shadow-zinc-950/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 active:translate-y-0 active:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:shadow-black/30 dark:hover:bg-white dark:hover:shadow-black/40 dark:focus-visible:outline-zinc-300 dark:active:bg-zinc-200">
                <flux:icon.funnel variant="micro" class="transition-transform duration-150 group-hover:scale-110" />
                {{ __('Filter') }}
            </button>
        </form>

        <div data-library-results class="transition-opacity duration-150">
            @include('stories.partials.list', ['stories' => $stories])
        </div>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-library-filter-form]');
            const results = document.querySelector('[data-library-results]');
            const count = document.querySelector('[data-library-count]');

            if (!form || !results || !count) return;

            let controller;

            const buildUrl = (pageUrl = null) => {
                const url = new URL(pageUrl || form.action, window.location.origin);
                const data = new FormData(form);

                url.search = '';

                for (const [key, value] of data.entries()) {
                    if (String(value).trim() !== '') {
                        url.searchParams.set(key, value);
                    }
                }

                if (pageUrl) {
                    const page = new URL(pageUrl, window.location.origin).searchParams.get('page');

                    if (page) {
                        url.searchParams.set('page', page);
                    }
                }

                return url;
            };

            const fetchResults = async (pageUrl = null) => {
                controller?.abort();
                controller = new AbortController();

                results.classList.add('opacity-50');

                try {
                    const response = await fetch(buildUrl(pageUrl), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: controller.signal,
                    });

                    if (!response.ok) return;

                    const payload = await response.json();

                    count.textContent = payload.count;
                    results.innerHTML = payload.html;
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                } finally {
                    results.classList.remove('opacity-50');
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                fetchResults();
            });

            const closeSelect = (select) => {
                const button = select.querySelector('[data-custom-select-button]');
                const menu = select.querySelector('[data-custom-select-menu]');

                button.setAttribute('aria-expanded', 'false');
                menu.classList.add('hidden');
            };

            const closeOtherSelects = (currentSelect) => {
                form.querySelectorAll('[data-custom-select]').forEach((select) => {
                    if (select !== currentSelect) {
                        closeSelect(select);
                    }
                });
            };

            form.querySelectorAll('[data-custom-select]').forEach((select) => {
                const input = select.querySelector('input[type="hidden"]');
                const button = select.querySelector('[data-custom-select-button]');
                const label = select.querySelector('[data-custom-select-label]');
                const menu = select.querySelector('[data-custom-select-menu]');

                button.addEventListener('click', () => {
                    const isOpen = button.getAttribute('aria-expanded') === 'true';

                    closeOtherSelects(select);
                    button.setAttribute('aria-expanded', String(!isOpen));
                    menu.classList.toggle('hidden', isOpen);
                });

                select.querySelectorAll('[data-custom-select-option]').forEach((option) => {
                    option.addEventListener('click', () => {
                        input.value = option.dataset.value;
                        label.textContent = option.textContent.trim();
                        closeSelect(select);
                        fetchResults();
                    });
                });
            });

            results.addEventListener('click', (event) => {
                const link = event.target.closest('a[href*="page="]');

                if (!link) return;

                event.preventDefault();
                fetchResults(link.href);
            });

            document.querySelectorAll('[data-library-genre]').forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    form.elements.genre.value = link.dataset.libraryGenre;
                    const genreSelect = form.elements.genre.closest('[data-custom-select]');
                    const selectedOption = genreSelect.querySelector(`[data-custom-select-option][data-value="${CSS.escape(link.dataset.libraryGenre)}"]`);

                    if (selectedOption) {
                        genreSelect.querySelector('[data-custom-select-label]').textContent = selectedOption.textContent.trim();
                    }

                    fetchResults();
                });
            });

            document.addEventListener('click', (event) => {
                if (event.target.closest('[data-custom-select]')) return;

                form.querySelectorAll('[data-custom-select]').forEach(closeSelect);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;

                form.querySelectorAll('[data-custom-select]').forEach(closeSelect);
            });
        })();
    </script>
</x-layouts::public>
