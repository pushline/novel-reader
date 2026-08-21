<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use App\Models\Story;
use App\Services\RepeatedChapterBlockDetector;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;

#[Description('Check novel chapters for repeated blocks of paragraphs.')]
class CheckNovelDuplicateContent extends Command
{
    protected $signature = 'novels:check-duplicates
        {--story-slug= : Story slug to check}
        {--start=1 : First chapter number}
        {--end= : Last chapter number, or the latest chapter when omitted}
        {--minimum-paragraphs=5 : Minimum consecutive repeated paragraphs}
        {--minimum-words=50 : Minimum words in the repeated block}';

    /**
     * Execute the console command.
     */
    public function handle(RepeatedChapterBlockDetector $detector): int
    {
        $slug = trim((string) $this->option('story-slug'));
        $start = (int) $this->option('start');
        $endOption = trim((string) $this->option('end'));
        $minimumParagraphs = (int) $this->option('minimum-paragraphs');
        $minimumWords = (int) $this->option('minimum-words');

        if ($slug === '' || $start < 1 || $minimumParagraphs < 2 || $minimumWords < 1) {
            $this->error('Provide --story-slug and positive range/threshold options.');

            return self::FAILURE;
        }

        $story = Story::query()->where('slug', $slug)->first();

        if (! $story) {
            $this->error("Story '{$slug}' was not found.");

            return self::FAILURE;
        }

        $end = $endOption === ''
            ? (int) $story->chapters()->max('number')
            : (int) $endOption;

        if ($end < $start) {
            $this->error('The ending chapter must be greater than or equal to the starting chapter.');

            return self::FAILURE;
        }

        $chapters = Chapter::query()
            ->where('story_id', $story->id)
            ->whereBetween('number', [$start, $end])
            ->orderBy('number')
            ->get(['id', 'number', 'content']);
        $rows = [];

        foreach ($chapters as $chapter) {
            foreach ($detector->detect($chapter->content, $minimumParagraphs, $minimumWords) as $match) {
                $rows[] = [
                    $chapter->number,
                    ($match['first_start'] + 1).'-'.($match['first_end'] + 1),
                    ($match['second_start'] + 1).'-'.($match['second_end'] + 1),
                    $match['paragraph_count'],
                    $match['word_count'],
                    $match['excerpt'],
                ];
            }
        }

        if ($rows === []) {
            $this->info("No repeated paragraph blocks found in {$chapters->count()} chapters ({$start}-{$end}).");

            return self::SUCCESS;
        }

        $this->table(
            ['Chapter', 'First paragraphs', 'Repeated paragraphs', 'Paragraphs', 'Words', 'Opening text'],
            $rows
        );
        $this->error(count($rows).' repeated block(s) found.');

        return self::FAILURE;
    }
}
