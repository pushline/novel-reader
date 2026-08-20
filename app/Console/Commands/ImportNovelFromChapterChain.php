<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Story;
use App\Services\ChapterHtmlExtractors\ChapterChainNavigator;
use App\Services\ChapterHtmlExtractors\ChapterHtmlExtractorManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

#[Description('Import novel chapters by following the next-chapter link on each page.')]
class ImportNovelFromChapterChain extends Command
{
    private const BROWSER_HEADERS = [
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.9',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
    ];

    protected $signature = 'novels:import-from-chapter-chain
        {--story-slug= : Existing or new story slug}
        {--title= : Story title when creating the story}
        {--cover-path= : Public cover path stored on the story, e.g. covers/story.webp}
        {--author=* : Author name attached to the story, repeatable}
        {--genre=* : Genre name attached to the story, repeatable}
        {--status=ongoing : Story status stored on the story}
        {--start-url= : URL of the first chapter to import}
        {--end= : Stop after this chapter number}
        {--delay-ms=1500 : Delay between requests}
        {--timeout-seconds=60 : Per-request timeout}
        {--retries=5 : HTTP attempts per chapter}
        {--retry-delay-ms=2000 : Delay between retry attempts}
        {--transport=auto : HTTP transport: auto, laravel, or curl}
        {--dry-run : Fetch and parse without saving}
        {--only-missing : Skip saving chapters that already exist}
        {--force : Update even when the import hash is unchanged}';

    /**
     * Execute the console command.
     */
    public function handle(ChapterHtmlExtractorManager $extractors): int
    {
        $slug = (string) $this->option('story-slug');
        $title = (string) $this->option('title');
        $startUrl = $this->normalizeStartUrl((string) $this->option('start-url'));
        $end = (int) $this->option('end');
        $delayMs = max(0, (int) $this->option('delay-ms'));
        $timeoutSeconds = max(1, (int) $this->option('timeout-seconds'));
        $retries = max(1, (int) $this->option('retries'));
        $retryDelayMs = max(0, (int) $this->option('retry-delay-ms'));
        $transport = strtolower((string) $this->option('transport'));

        if ($slug === '' || $startUrl === '' || $end < 1 || ! in_array($transport, ['auto', 'laravel', 'curl'], true)) {
            $this->error('Provide --story-slug, --start-url, --end, and a valid --transport.');

            return self::FAILURE;
        }

        $urlTemplate = $this->urlTemplate($startUrl);

        $extractor = $extractors->extractorFor($startUrl);

        if (! $extractor instanceof ChapterChainNavigator) {
            $host = parse_url($startUrl, PHP_URL_HOST) ?: 'unknown host';

            $this->error("Chapter chain navigation is not supported for {$host}.");

            return self::FAILURE;
        }

        $story = $this->resolveStory($slug, $title);

        $dryRun = (bool) $this->option('dry-run');
        $onlyMissing = (bool) $this->option('only-missing');
        $force = (bool) $this->option('force');

        $url = $startUrl;
        $expectedNumber = null;
        $visited = [];

        while (true) {
            if (isset($visited[$url])) {
                $this->error("Chapter chain looped back to {$url}; stopping.");

                break;
            }

            $visited[$url] = true;

            try {
                $html = $this->fetchChapter($url, $transport, $retries, $retryDelayMs, $timeoutSeconds);
                $navigation = $extractor->chapterNavigation($html);
                $skip = $navigation['skip'] ?? false;
                $number = $navigation['number'] ?? ($skip ? null : $expectedNumber);

                if ($number === null && ! $skip) {
                    $this->error("Could not determine the chapter number for {$url}; stopping.");

                    break;
                }

                if ($number !== null && $number > $end) {
                    break;
                }

                if ($skip) {
                    $label = $navigation['label'] ?? $url;
                    $this->line("Source entry '{$label}': skip unnumbered content");
                } else {
                    $this->saveChapter($extractor->extract($html), $story, $number, $url, [
                        'dry-run' => $dryRun,
                        'only-missing' => $onlyMissing,
                        'force' => $force,
                    ]);
                }

                $next = $navigation['next'];

                if ($next === null) {
                    $label = $number !== null ? "Chapter {$number}" : ($navigation['label'] ?? $url);
                    $this->line("{$label}: no next chapter link; stopping.");

                    break;
                }

                $expectedNumber = $next['number'] ?? ($number !== null ? $number + 1 : $expectedNumber);

                if ($expectedNumber !== null && $expectedNumber > $end) {
                    break;
                }

                if (isset($next['url']) && $next['url'] !== '') {
                    $url = $next['url'];
                } elseif ($urlTemplate !== null && $expectedNumber !== null) {
                    $url = $this->chapterUrl($urlTemplate, $next['id'], $expectedNumber);
                } else {
                    $this->error("Chapter {$number}: the source did not provide a usable next chapter URL; stopping.");

                    break;
                }
            } catch (\Throwable $exception) {
                $label = $expectedNumber !== null ? "Chapter {$expectedNumber}" : $url;

                $this->error("{$label}: failed - {$exception->getMessage()}");

                break;
            }

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Create or update the story row, its cover, and its author and genre links
     * from the command options before any chapter is fetched.
     */
    private function resolveStory(string $slug, string $title): Story
    {
        $story = Story::firstOrCreate(
            ['slug' => $slug],
            ['title' => $title !== '' ? $title : Str::headline($slug), 'status' => 'ongoing']
        );

        $attributes = array_filter([
            'cover_path' => (string) $this->option('cover-path'),
            'status' => (string) $this->option('status'),
        ], fn (string $value): bool => $value !== '');

        $story->forceFill($attributes)->save();

        $authors = $this->names($this->option('author'));

        if ($authors !== []) {
            $story->authors()->syncWithoutDetaching(
                array_map(
                    fn (string $name): int => Author::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id,
                    $authors,
                )
            );
        }

        $genres = $this->names($this->option('genre'));

        if ($genres !== []) {
            $story->genres()->sync(
                array_map(
                    fn (string $name): int => Genre::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id,
                    $genres,
                )
            );
        }

        return $story;
    }

    /**
     * @param  mixed  $values
     * @return list<string>
     */
    private function names($values): array
    {
        $names = array_map(fn ($value): string => trim((string) $value), is_array($values) ? $values : []);

        return array_values(array_unique(array_filter($names, fn (string $name): bool => $name !== '')));
    }

    /**
     * @param  array{title: string|null, content: string}  $data
     * @param  array{dry-run: bool, only-missing: bool, force: bool}  $flags
     */
    private function saveChapter(array $data, Story $story, int $number, string $url, array $flags): void
    {
        $existing = Chapter::query()
            ->where('story_id', $story->id)
            ->where('number', $number)
            ->first();

        if ($existing && $flags['only-missing']) {
            $this->line("Chapter {$number}: skip existing");

            return;
        }

        $hash = hash('sha256', $data['content']);

        if ($existing && $existing->import_hash === $hash && ! $flags['force']) {
            $this->line("Chapter {$number}: skip unchanged");

            return;
        }

        $payload = [
            'title' => "Chapter {$number}",
            'content' => $data['content'],
            'word_count' => str_word_count(strip_tags($data['content'])),
            'source_url' => $url,
            'import_hash' => $hash,
            'imported_at' => now(),
        ];

        if ($flags['dry-run']) {
            $this->info("Chapter {$number}: dry-run ok ({$payload['word_count']} words)");

            return;
        }

        Chapter::updateOrCreate(
            ['story_id' => $story->id, 'number' => $number],
            $payload
        );

        $this->info("Chapter {$number}: saved");
    }

    /**
     * Turn ".../<chapter-id>/<chapter-slug>" into a template the importer can
     * rebuild once the next chapter id is known.
     */
    private function urlTemplate(string $startUrl): ?string
    {
        $replaced = preg_replace('#/\d+/[^/?\#]+$#', '/{id}/chapter-{chapter}', $startUrl, 1, $count);

        return $count === 1 ? $replaced : null;
    }

    private function chapterUrl(string $template, string $id, int $number): string
    {
        return str_replace(['{id}', '{chapter}'], [$id, (string) $number], $template);
    }

    private function normalizeStartUrl(string $url): string
    {
        $url = trim($url);

        if (preg_match('/^\[([^\]]+)]\(([^)]+)\)$/', $url, $matches) === 1 && $matches[1] === $matches[2]) {
            $url = $matches[1];
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($host === 'webnovel.com' || str_ends_with($host, '.webnovel.com')) {
            if (preg_match('#/book/(\d+)/(\d+)$#', $path, $matches) === 1) {
                return "https://en.webnovel.com/book/{$matches[1]}/{$matches[2]}";
            }

            if (preg_match('#_(\d+)/[^/]*_(\d+)$#', $path, $matches) === 1) {
                return "https://en.webnovel.com/book/{$matches[1]}/{$matches[2]}";
            }
        }

        return $url;
    }

    private function fetchChapter(
        string $url,
        string $transport,
        int $retries,
        int $retryDelayMs,
        int $timeoutSeconds,
    ): string {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $useCurl = $transport === 'curl'
            || ($transport === 'auto' && ($host === 'webnovel.com' || str_ends_with($host, '.webnovel.com')));

        if ($useCurl) {
            return $this->fetchWithCurl($url, $retries, $retryDelayMs, $timeoutSeconds);
        }

        return Http::withHeaders(self::BROWSER_HEADERS)
            ->retry($retries, $retryDelayMs)
            ->connectTimeout(20)
            ->timeout($timeoutSeconds)
            ->get($url)
            ->throw()
            ->body();
    }

    private function fetchWithCurl(string $url, int $retries, int $retryDelayMs, int $timeoutSeconds): string
    {
        $binary = PHP_OS_FAMILY === 'Windows' ? 'curl.exe' : 'curl';
        $lastError = 'unknown cURL error';

        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            $process = new Process([
                $binary,
                '--location',
                '--silent',
                '--show-error',
                '--fail-with-body',
                '--connect-timeout',
                '20',
                '--max-time',
                (string) $timeoutSeconds,
                '--user-agent',
                self::BROWSER_HEADERS['User-Agent'],
                '--header',
                'Accept: '.self::BROWSER_HEADERS['Accept'],
                '--header',
                'Accept-Language: '.self::BROWSER_HEADERS['Accept-Language'],
                $url,
            ]);
            $process->setTimeout($timeoutSeconds + 5);
            $process->run();

            if ($process->isSuccessful()) {
                return $process->getOutput();
            }

            $lastError = trim($process->getErrorOutput());

            if ($lastError === '') {
                $lastError = trim(strip_tags($process->getOutput()));
            }

            if ($attempt < $retries && $retryDelayMs > 0) {
                usleep($retryDelayMs * 1000);
            }
        }

        throw new \RuntimeException('System cURL request failed: '.Str::limit($lastError, 300));
    }
}
