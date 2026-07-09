<?php

use App\Models\Chapter;
use App\Models\Story;
use Illuminate\Support\Facades\Http;

it('refuses to import without authorization confirmation', function () {
    $this->artisan('novels:import-from-url-pattern', [
        '--story-slug' => 'overgeared',
        '--url-pattern' => 'https://example.com/chapter-{chapter}.html',
        '--start' => 1,
        '--end' => 1,
    ])->assertExitCode(1);
});

it('imports authorized chapter html from a url pattern', function () {
    Http::fake([
        'example.com/*' => Http::response(<<<'HTML'
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
        '--authorized' => true,
        '--story-slug' => 'overgeared',
        '--title' => 'Overgeared',
        '--url-pattern' => 'https://example.com/chapter-{chapter}.html',
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
