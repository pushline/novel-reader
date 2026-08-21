<?php

use App\Models\Chapter;
use App\Models\Story;
use Illuminate\Support\Facades\Http;

it('seeds metadata and runs every configured novel import job', function () {
    config()->set('novel_imports.stories', [
        'overgeared' => [
            'title' => 'Overgeared',
            'jobs' => [[
                'label' => 'test chapter',
                'command' => 'novels:import-from-url-pattern',
                'options' => [
                    '--story-slug' => 'overgeared',
                    '--url-pattern' => 'https://novelfull.com/overgeared/chapter-{chapter}.html',
                    '--start' => 1,
                    '--end' => 1,
                ],
            ]],
        ],
    ]);

    Http::fake([
        'novelfull.com/overgeared/chapter-1.html' => Http::response(<<<'HTML'
            <html><body>
                <h1>Chapter 1</h1>
                <div id="chapter-content"><p>The imported chapter body.</p></div>
            </body></html>
            HTML),
    ]);

    $this->artisan('novels:seedall', ['--delay-ms' => 0])
        ->expectsOutputToContain('All 1 novel import jobs completed successfully.')
        ->assertExitCode(0);

    $story = Story::query()->where('slug', 'overgeared')->firstOrFail();

    expect($story->authors()->pluck('name')->all())->toBe(['Park Saenal'])
        ->and(Chapter::query()->whereBelongsTo($story)->firstOrFail()->content)
        ->toContain('The imported chapter body.');
});

it('preserves existing chapters unless refresh is requested', function () {
    config()->set('novel_imports.stories', [
        'overgeared' => [
            'title' => 'Overgeared',
            'jobs' => [[
                'label' => 'test chapter',
                'command' => 'novels:import-from-url-pattern',
                'options' => [
                    '--story-slug' => 'overgeared',
                    '--url-pattern' => 'https://novelfull.com/overgeared/chapter-{chapter}.html',
                    '--start' => 1,
                    '--end' => 1,
                ],
            ]],
        ],
    ]);

    $story = Story::factory()->create(['slug' => 'overgeared']);
    Chapter::factory()->for($story)->create([
        'number' => 1,
        'content' => '<p>Keep this corrected content.</p>',
    ]);
    Http::fake();

    $this->artisan('novels:seedall', ['--delay-ms' => 0])->assertExitCode(0);

    expect($story->chapters()->firstOrFail()->content)->toBe('<p>Keep this corrected content.</p>');
    Http::assertNothingSent();
});
