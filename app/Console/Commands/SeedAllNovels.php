<?php

namespace App\Console\Commands;

use Database\Seeders\StorySeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;

#[Description('Seed story metadata and import every configured novel.')]
class SeedAllNovels extends Command
{
    protected $signature = 'novels:seedall
        {--delay-ms=1500 : Delay between chapter requests}
        {--timeout-seconds=60 : Per-request timeout}
        {--retries=5 : HTTP attempts per chapter}
        {--retry-delay-ms=2000 : Delay between retry attempts}
        {--transport=auto : Chain-import HTTP transport: auto, laravel, or curl}
        {--refresh : Re-fetch existing chapters instead of only importing missing ones}
        {--dry-run : Fetch and parse without saving chapters}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $catalog = config('novel_imports.stories');

        if (! is_array($catalog) || $catalog === []) {
            $this->error('No novel imports are configured in config/novel_imports.php.');

            return self::FAILURE;
        }

        $this->components->info('Seeding story metadata');

        if ($this->call('db:seed', ['--class' => StorySeeder::class, '--force' => true]) !== self::SUCCESS) {
            $this->error('Story metadata seeding failed.');

            return self::FAILURE;
        }

        $failures = [];
        $jobCount = 0;

        foreach ($catalog as $slug => $story) {
            if (! is_string($slug) || ! is_array($story)) {
                $failures[] = 'Invalid story entry in the import catalog.';

                continue;
            }

            $title = is_string($story['title'] ?? null) ? $story['title'] : $slug;
            $jobs = $story['jobs'] ?? null;

            if (! is_array($jobs) || $jobs === []) {
                $failures[] = "{$title}: no import jobs configured.";

                continue;
            }

            $this->newLine();
            $this->components->twoColumnDetail("<fg=cyan>{$title}</>", '<fg=yellow>starting</>');

            foreach ($jobs as $job) {
                if (! is_array($job) || ! $this->validJob($job)) {
                    $failures[] = "{$title}: invalid import job configuration.";

                    continue;
                }

                $jobCount++;
                $label = $job['label'];
                $command = $job['command'];
                $options = $this->optionsFor($command, $job['options']);

                $this->components->twoColumnDetail("  {$label}", '<fg=yellow>running</>');
                $exitCode = $this->call($command, $options);

                if ($exitCode !== self::SUCCESS) {
                    $failures[] = "{$title}: {$label}.";
                    $this->components->twoColumnDetail("  {$label}", '<fg=red>failed</>');
                } else {
                    $this->components->twoColumnDetail("  {$label}", '<fg=green>done</>');
                }
            }
        }

        $this->newLine();

        if ($failures !== []) {
            $this->error(count($failures).' import job(s) failed:');

            foreach ($failures as $failure) {
                $this->line(" - {$failure}");
            }

            return self::FAILURE;
        }

        $this->info("All {$jobCount} novel import jobs completed successfully.");

        return self::SUCCESS;
    }

    /**
     * @param  array<mixed>  $job
     */
    private function validJob(array $job): bool
    {
        return isset($job['label'], $job['command'], $job['options'])
            && is_string($job['label'])
            && in_array($job['command'], [
                'novels:import-from-url-pattern',
                'novels:import-from-chapter-chain',
            ], true)
            && is_array($job['options']);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function optionsFor(string $command, array $options): array
    {
        $options['--delay-ms'] = max(0, (int) $this->option('delay-ms'));
        $options['--timeout-seconds'] = max(1, (int) $this->option('timeout-seconds'));
        $options['--retries'] = max(1, (int) $this->option('retries'));
        $options['--retry-delay-ms'] = max(0, (int) $this->option('retry-delay-ms'));

        if (! $this->option('refresh')) {
            $options['--only-missing'] = true;
        }

        if ($this->option('dry-run')) {
            $options['--dry-run'] = true;
        }

        if ($command === 'novels:import-from-chapter-chain') {
            $options['--transport'] = (string) $this->option('transport');
        }

        return $options;
    }
}
