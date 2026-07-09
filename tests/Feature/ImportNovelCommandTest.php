<?php

use App\Models\Chapter;
use App\Models\Story;
use Illuminate\Support\Facades\Http;

it('imports chapter html from a url pattern', function () {
    Http::fake([
        'novelfull.com/*' => Http::response(<<<'HTML'
            <html>
                <body>
                    <h1>1 . Overgeared</h1>
                    <div id="chapter-content">
                        <p onclick="bad()">The rain fell.</p>
                        <div class="ads"><p>Buy now</p></div>
                        <script>alert("x")</script>
                        <iframe src="https://example.com/ad"></iframe>
                    </div>
                </body>
            </html>
            HTML),
    ]);

    $this->artisan('novels:import-from-url-pattern', [
        '--story-slug' => 'overgeared',
        '--title' => 'Overgeared',
        '--url-pattern' => 'https://novelfull.com/overgeared/chapter-{chapter}.html',
        '--start' => 1,
        '--end' => 1,
        '--delay-ms' => 0,
    ])->assertExitCode(0);

    $story = Story::where('slug', 'overgeared')->firstOrFail();
    $chapter = Chapter::whereBelongsTo($story)->where('number', 1)->firstOrFail();

    expect($chapter->title)->toBe('Chapter 1')
        ->and($chapter->content)->toContain('The rain fell.')
        ->and($chapter->content)->not->toContain('script')
        ->and($chapter->content)->not->toContain('iframe')
        ->and($chapter->content)->not->toContain('Buy now')
        ->and($chapter->content)->not->toContain('onclick');
});

it('does not fetch unsupported source hosts', function () {
    Http::fake();

    $this->artisan('novels:import-from-url-pattern', [
        '--story-slug' => 'overgeared',
        '--title' => 'Overgeared',
        '--url-pattern' => 'https://example.com/chapter-{chapter}.html',
        '--start' => 1,
        '--end' => 1,
        '--delay-ms' => 0,
    ])->assertExitCode(0);

    Http::assertNothingSent();
});
