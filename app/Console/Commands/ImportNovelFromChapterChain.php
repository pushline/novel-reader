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

#[Description('Import novel chapters by following the next-chapter link on each page.')]
class ImportNovelFromChapterChain extends Command
{
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
        $startUrl = (string) $this->option('start-url');
        $end = (int) $this->option('end');
        $delayMs = max(0, (int) $this->option('delay-ms'));
        $timeoutSeconds = max(1, (int) $this->option('timeout-seconds'));
        $retries = max(1, (int) $this->option('retries'));
        $retryDelayMs = max(0, (int) $this->option('retry-delay-ms'));

        if ($slug === '' || $startUrl === '' || $end < 1) {
            $this->error('Provide --story-slug, --start-url, and --end.');

            return self::FAILURE;
        }

        $urlTemplate = $this->urlTemplate($startUrl);

        if ($urlTemplate === null) {
            $this->error('The --start-url must end with a numeric chapter id followed by a chapter slug.');

            return self::FAILURE;
        }

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
                $response = Http::retry($retries, $retryDelayMs)
                    ->connectTimeout(20)
                    ->timeout($timeoutSeconds)
                    ->get($url)
                    ->throw();

                $html = $response->body();
                $navigation = $extractor->chapterNavigation($html);
                $number = $navigation['number'] ?? $expectedNumber;

                if ($number === null) {
                    $this->error("Could not determine the chapter number for {$url}; stopping.");

                    break;
                }

                if ($number > $end) {
                    break;
                }

                $this->saveChapter($extractor->extract($html), $story, $number, $url, [
                    'dry-run' => $dryRun,
                    'only-missing' => $onlyMissing,
                    'force' => $force,
                ]);

                $next = $navigation['next'];

                if ($next === null) {
                    $this->line("Chapter {$number}: no next chapter link; stopping.");

                    break;
                }

                $expectedNumber = $next['number'] ?? $number + 1;

                if ($expectedNumber > $end) {
                    break;
                }

                $url = $this->chapterUrl($urlTemplate, $next['id'], $expectedNumber);
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
}
