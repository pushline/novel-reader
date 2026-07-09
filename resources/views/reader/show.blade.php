<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ ($settings['theme'] ?? 'dark') === 'light' ? '' : 'dark' }}">
    <head>
        @include('partials.head', ['title' => $chapter->title])
    </head>
    <body
        class="min-h-screen bg-zinc-100 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100"
        data-reader-settings='@json($settings)'
        data-authenticated='@json(auth()->check())'
        data-progress-url="{{ route('progress.store', $chapter) }}"
    >
        <header class="sticky top-0 z-20 border-b border-zinc-200 bg-stone-50/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95">
            <div class="h-1 bg-zinc-200 dark:bg-zinc-800">
                <div id="reader-progress-bar" class="h-full bg-emerald-500 transition-[width]" style="width: {{ $progress?->scroll_percent ?? 0 }}%"></div>
            </div>
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-3 px-4 py-3">
                <a href="{{ route('stories.show', $story) }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">{{ $story->title }}</a>
                <span class="hidden text-zinc-400 sm:block">/</span>
                <select class="h-9 min-w-0 flex-1 rounded-md border border-zinc-300 bg-white px-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" onchange="window.location.href = this.value">
                    @foreach ($chapters as $option)
                        <option value="{{ route('chapters.show', [$story, $option->number]) }}" @selected($option->id === $chapter->id)>{{ $option->number }}. {{ $option->title }}</option>
                    @endforeach
                </select>
                <button id="theme-toggle" type="button" class="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-md border border-zinc-300 px-3 text-sm transition duration-150 ease-out hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 dark:border-zinc-700 dark:hover:bg-zinc-900 dark:focus-visible:outline-zinc-300">
                    <flux:icon.sun variant="micro" class="hidden dark:inline-block" />
                    <flux:icon.moon variant="micro" class="dark:hidden" />
                    <span id="theme-toggle-label">{{ ($settings['theme'] ?? 'dark') === 'dark' ? __('Light') : __('Dark') }}</span>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex h-9 items-center gap-1.5 rounded-md border border-zinc-300 px-3 text-sm text-zinc-900 transition duration-150 ease-out hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-900 dark:focus-visible:outline-zinc-300">
                        <flux:icon.sparkles variant="micro" />
                        <span class="max-sm:hidden">{{ __('Dashboard') }}</span>
                    </a>
                    <form
                        method="POST"
                        action="{{ $bookmarked ? route('bookmarks.destroy', $chapter) : route('bookmarks.store', $chapter) }}"
                        data-bookmark-form
                        data-store-url="{{ route('bookmarks.store', $chapter) }}"
                        data-destroy-url="{{ route('bookmarks.destroy', $chapter) }}"
                        data-bookmarked="@json($bookmarked)"
                    >
                        @csrf
                        @if ($bookmarked)
                            @method('DELETE')
                        @endif
                        <button type="submit" data-bookmark-button class="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-md border border-zinc-300 px-3 text-sm transition duration-150 ease-out hover:bg-white disabled:cursor-wait disabled:opacity-70 dark:border-zinc-700 dark:hover:bg-zinc-900">
                            <flux:icon.bookmark variant="micro" data-bookmark-icon @class(['text-amber-500' => $bookmarked]) />
                            <span data-bookmark-label>{{ $bookmarked ? __('Saved') : __('Bookmark') }}</span>
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <main id="reader-shell" class="mx-auto px-4 py-8" style="max-width: {{ $settings['contentWidth'] }}px">
            <div class="mb-8 flex flex-col gap-5 border-b border-zinc-200 pb-6 dark:border-zinc-800">
                <div class="flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <a href="{{ route('home') }}" class="hover:text-zinc-950 dark:hover:text-white">{{ __('Library') }}</a>
                    <span>/</span>
                    <a href="{{ route('stories.show', $story) }}" class="hover:text-zinc-950 dark:hover:text-white">{{ $story->title }}</a>
                </div>
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Chapter') }} {{ $chapter->number }}</p>
                        <h1 class="mt-1 text-3xl font-semibold text-zinc-950 dark:text-zinc-50">{{ $chapter->title }}</h1>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1 rounded-sm bg-white px-2 py-1 dark:bg-zinc-900"><flux:icon.document-text variant="micro" /> {{ number_format($chapter->word_count) }} {{ __('words') }}</span>
                        <span class="inline-flex items-center gap-1 rounded-sm bg-white px-2 py-1 dark:bg-zinc-900"><flux:icon.list-bullet variant="micro" /> {{ $chapters->count() }} {{ __('chapters') }}</span>
                    </div>
                </div>
                @if ($previous || $next)
                    <nav class="grid gap-2 sm:grid-cols-2">
                        @if ($previous)
                            <a data-previous-chapter href="{{ route('chapters.show', [$story, $previous->number]) }}" class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-800 transition duration-150 ease-out hover:-translate-y-px hover:border-zinc-400 hover:bg-zinc-50 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 active:translate-y-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:border-zinc-600 dark:hover:bg-zinc-800 dark:focus-visible:outline-zinc-300">
                                <flux:icon.arrow-left variant="micro" />
                                {{ __('Previous') }}
                            </a>
                        @else
                            <span class="hidden sm:block"></span>
                        @endif

                        @if ($next)
                            <a data-next-chapter href="{{ route('chapters.show', [$story, $next->number]) }}" class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-md bg-zinc-950 px-4 py-2 text-sm font-medium text-white shadow-sm shadow-zinc-950/15 transition duration-150 ease-out hover:-translate-y-px hover:bg-zinc-800 hover:shadow-md hover:shadow-zinc-950/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 active:translate-y-0 active:bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-950 dark:shadow-black/30 dark:hover:bg-white dark:hover:shadow-black/40 dark:focus-visible:outline-zinc-300 dark:active:bg-zinc-200">
                                {{ __('Next') }}
                                <flux:icon.arrow-right variant="micro" />
                            </a>
                        @endif
                    </nav>
                @endif
                <div class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-3 shadow-sm shadow-zinc-200/50 dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-none sm:grid-cols-4">
                    <label class="grid gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon.magnifying-glass variant="micro" />
                            {{ __('Size') }}
                        </span>
                        <input id="reader-font-size" type="range" min="16" max="24" value="{{ $settings['fontSize'] }}" class="w-full">
                    </label>
                    <label class="grid gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon.bars-3-bottom-left variant="micro" />
                            {{ __('Line') }}
                        </span>
                        <input id="reader-line-height" type="range" min="1.45" max="2.1" step="0.05" value="{{ $settings['lineHeight'] }}" class="w-full">
                    </label>
                    <label class="grid gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon.arrows-right-left variant="micro" />
                            {{ __('Width') }}
                        </span>
                        <input id="reader-content-width" type="range" min="620" max="980" step="20" value="{{ $settings['contentWidth'] }}" class="w-full">
                    </label>
                    <label class="grid gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon.language variant="micro" />
                            {{ __('Font') }}
                        </span>
                        <select id="reader-font-family" class="h-8 rounded-md border border-zinc-300 bg-white px-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            <option value="serif" @selected($settings['fontFamily'] === 'serif')>Serif</option>
                            <option value="sans" @selected($settings['fontFamily'] === 'sans')>Sans</option>
                            <option value="mono" @selected($settings['fontFamily'] === 'mono')>Mono</option>
                            <option value="open-sans" @selected($settings['fontFamily'] === 'open-sans')>Open Sans</option>
                            <option value="google-sans" @selected($settings['fontFamily'] === 'google-sans')>Google Sans</option>
                            <option value="roboto" @selected($settings['fontFamily'] === 'roboto')>Roboto</option>
                            <option value="montserrat" @selected($settings['fontFamily'] === 'montserrat')>Montserrat</option>
                            <option value="nunito" @selected($settings['fontFamily'] === 'nunito')>Nunito</option>
                        </select>
                    </label>
                </div>
            </div>

            <article
                id="reader-content"
                class="reader-content font-{{ $settings['fontFamily'] }}"
                style="font-size: {{ $settings['fontSize'] }}px; line-height: {{ $settings['lineHeight'] }}"
            >
                {!! $chapter->content !!}
            </article>

            <nav class="mt-12 grid gap-3 border-t border-zinc-200 pt-6 dark:border-zinc-800 sm:grid-cols-2">
                @if ($previous)
                    <a data-previous-chapter href="{{ route('chapters.show', [$story, $previous->number]) }}" class="inline-flex min-h-12 items-center justify-center gap-1.5 rounded-md border border-zinc-300 px-4 py-2 text-sm hover:bg-white dark:border-zinc-700 dark:hover:bg-zinc-900">
                        <flux:icon.arrow-left variant="micro" />
                        {{ __('Previous') }}
                    </a>
                @else
                    <span class="hidden sm:block"></span>
                @endif
                @if ($next)
                    <a data-next-chapter href="{{ route('chapters.show', [$story, $next->number]) }}" class="inline-flex min-h-12 items-center justify-center gap-1.5 rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-950">
                        {{ __('Next') }}
                        <flux:icon.arrow-right variant="micro" />
                    </a>
                @endif
            </nav>
        </main>

        <script>
            (() => {
                const body = document.body;
                const authenticated = body.dataset.authenticated === 'true' || body.dataset.authenticated === '1';
                const progressUrl = body.dataset.progressUrl;
                const defaults = JSON.parse(body.dataset.readerSettings || '{}');
                const saved = authenticated ? {} : JSON.parse(localStorage.getItem('reader-settings') || '{}');
                const state = { ...defaults, ...saved };
                const shell = document.getElementById('reader-shell');
                const content = document.getElementById('reader-content');
                const progressBar = document.getElementById('reader-progress-bar');
                const themeToggle = document.getElementById('theme-toggle');
                const themeToggleLabel = document.getElementById('theme-toggle-label');
                const bookmarkForm = document.querySelector('[data-bookmark-form]');
                const inputs = {
                    fontSize: document.getElementById('reader-font-size'),
                    lineHeight: document.getElementById('reader-line-height'),
                    contentWidth: document.getElementById('reader-content-width'),
                    fontFamily: document.getElementById('reader-font-family'),
                };
                let progressTimer = null;

                const apply = () => {
                    document.documentElement.classList.toggle('dark', state.theme === 'dark');
                    themeToggleLabel.textContent = state.theme === 'dark' ? 'Light' : 'Dark';
                    shell.style.maxWidth = `${state.contentWidth}px`;
                    content.style.fontSize = `${state.fontSize}px`;
                    content.style.lineHeight = state.lineHeight;
                    content.classList.remove('font-serif', 'font-sans', 'font-mono', 'font-open-sans', 'font-google-sans', 'font-roboto', 'font-montserrat', 'font-nunito');
                    content.classList.add(`font-${state.fontFamily || 'serif'}`);

                    Object.entries(inputs).forEach(([key, input]) => {
                        input.value = state[key];
                    });

                    if (!authenticated) {
                        localStorage.setItem('reader-settings', JSON.stringify(state));
                    }
                };

                themeToggle.addEventListener('click', () => {
                    state.theme = state.theme === 'dark' ? 'light' : 'dark';
                    apply();
                });

                Object.entries(inputs).forEach(([key, input]) => {
                    input.addEventListener('input', () => {
                        state[key] = input.type === 'range' ? Number(input.value) : input.value;
                        apply();
                    });
                });

                if (bookmarkForm) {
                    const button = bookmarkForm.querySelector('[data-bookmark-button]');
                    const icon = bookmarkForm.querySelector('[data-bookmark-icon]');
                    const label = bookmarkForm.querySelector('[data-bookmark-label]');

                    const setBookmarkState = (bookmarked) => {
                        bookmarkForm.dataset.bookmarked = bookmarked ? 'true' : 'false';
                        bookmarkForm.action = bookmarked ? bookmarkForm.dataset.destroyUrl : bookmarkForm.dataset.storeUrl;
                        label.textContent = bookmarked ? 'Saved' : 'Bookmark';
                        icon.classList.toggle('text-amber-500', bookmarked);

                        let method = bookmarkForm.querySelector('input[name="_method"]');

                        if (bookmarked && !method) {
                            method = document.createElement('input');
                            method.type = 'hidden';
                            method.name = '_method';
                            bookmarkForm.appendChild(method);
                        }

                        if (method) {
                            if (bookmarked) {
                                method.value = 'DELETE';
                            } else {
                                method.remove();
                            }
                        }
                    };

                    bookmarkForm.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        if (button.disabled) return;

                        button.disabled = true;

                        try {
                            const response = await fetch(bookmarkForm.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: new FormData(bookmarkForm),
                            });

                            if (!response.ok) throw new Error('Bookmark request failed.');

                            const data = await response.json();

                            setBookmarkState(data.bookmarked);
                        } catch (error) {
                            console.error(error);
                        } finally {
                            button.disabled = false;
                        }
                    });
                }

                window.addEventListener('keydown', (event) => {
                    if (event.target.matches('input, select, textarea')) return;
                    if (event.key === 'ArrowLeft') document.querySelector('[data-previous-chapter]')?.click();
                    if (event.key === 'ArrowRight') document.querySelector('[data-next-chapter]')?.click();
                });

                window.addEventListener('scroll', () => {
                    const max = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
                    const scroll_percent = Math.min(100, Math.round((window.scrollY / max) * 100));
                    progressBar.style.width = `${scroll_percent}%`;

                    if (!authenticated) return;

                    clearTimeout(progressTimer);
                    progressTimer = setTimeout(() => {
                        fetch(progressUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ scroll_percent }),
                        });
                    }, 700);
                }, { passive: true });

                apply();
            })();
        </script>
    </body>
</html>
