<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use App\Models\Story;
use App\Services\ChapterHtmlExtractors\ChapterHtmlExtractorManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

#[Description('Import novel chapters from a numbered URL pattern.')]
class ImportNovelFromUrlPattern extends Command
{
    protected $signature = 'novels:import-from-url-pattern
        {--story-slug= : Existing or new story slug}
        {--title= : Story title when creating the story}
        {--url-pattern= : URL containing {chapter}}
        {--start=1 : First chapter number}
        {--end= : Last chapter number}
        {--delay-ms=1500 : Delay between requests}
        {--timeout-seconds=60 : Per-request timeout}
        {--retries=5 : HTTP attempts per chapter}
        {--retry-delay-ms=2000 : Delay between retry attempts}
        {--dry-run : Fetch and parse without saving}
        {--only-missing : Skip chapters that already exist}
        {--force : Update even when the import hash is unchanged}';

    /**
     * Execute the console command.
     */
    public function handle(ChapterHtmlExtractorManager $extractors): int
    {
        $slug = (string) $this->option('story-slug');
        $title = (string) $this->option('title');
        $pattern = (string) $this->option('url-pattern');
        $start = (int) $this->option('start');
        $end = (int) $this->option('end');
        $delayMs = max(0, (int) $this->option('delay-ms'));
        $timeoutSeconds = max(1, (int) $this->option('timeout-seconds'));
        $retries = max(1, (int) $this->option('retries'));
        $retryDelayMs = max(0, (int) $this->option('retry-delay-ms'));

        if ($slug === '' || $pattern === '' || $end < $start || ! str_contains($pattern, '{chapter}')) {
            $this->error('Provide --story-slug, --url-pattern with {chapter}, and a valid --start/--end range.');

            return self::FAILURE;
        }

        $story = Story::firstOrCreate(
            ['slug' => $slug],
            ['title' => $title !== '' ? $title : Str::headline($slug), 'status' => 'ongoing']
        );

        $dryRun = (bool) $this->option('dry-run');
        $onlyMissing = (bool) $this->option('only-missing');
        $force = (bool) $this->option('force');

        for ($number = $start; $number <= $end; $number++) {
            $existing = Chapter::query()
                ->where('story_id', $story->id)
                ->where('number', $number)
                ->first();

            if ($onlyMissing && $existing) {
                $this->line("Chapter {$number}: skip existing");

                continue;
            }

            $url = str_replace('{chapter}', (string) $number, $pattern);

            try {
                if (! $extractors->supports($url)) {
                    $host = parse_url($url, PHP_URL_HOST) ?: 'unknown host';

                    $this->error("Chapter {$number}: unsupported source host {$host}");

                    continue;
                }

                $response = Http::retry($retries, $retryDelayMs)
                    ->connectTimeout(20)
                    ->timeout($timeoutSeconds)
                    ->get($url)
                    ->throw();

                $data = $extractors->extract($url, $response->body());
                $hash = hash('sha256', $data['content']);

                if ($existing && $existing->import_hash === $hash && ! $force) {
                    $this->line("Chapter {$number}: skip unchanged");

                    continue;
                }

                $payload = [
                    'title' => "Chapter {$number}",
                    'content' => $data['content'],
                    'word_count' => str_word_count(strip_tags($data['content'])),
                    'source_url' => $url,
                    'import_hash' => $hash,
                    'imported_at' => now(),
                ];

                if ($dryRun) {
                    $this->info("Chapter {$number}: dry-run ok ({$payload['word_count']} words)");

                    continue;
                }

                Chapter::updateOrCreate(
                    ['story_id' => $story->id, 'number' => $number],
                    $payload
                );

                $this->info("Chapter {$number}: saved");
            } catch (\Throwable $exception) {
                $this->error("Chapter {$number}: failed - {$exception->getMessage()}");
            }

            if ($delayMs > 0 && $number < $end) {
                usleep($delayMs * 1000);
            }
        }

        return self::SUCCESS;
    }
}
