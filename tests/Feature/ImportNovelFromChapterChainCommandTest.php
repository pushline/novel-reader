<?php

use App\Models\Chapter;
use App\Models\Story;
use Illuminate\Support\Facades\Http;

function revengerChapterPage(int $number, ?int $nextId, ?int $nextNumber): string
{
    $next = $nextId === null
        ? '<button id="nextBtn" data-next-chapter-id="" data-next-chapter-number="0" disabled></button>'
        : '<button id="nextBtn" data-next-chapter-id="'.$nextId.'" data-next-chapter-number="'.$nextNumber.'"></button>';

    return <<<HTML
        <html>
            <body>
                <h1 class="chapter-title">Chapter {$number} : </h1>
                <section id="chapterContent" class="chapter-content">
                    <div id="contentWrapper">
                        <p onclick="bad()">Body of chapter {$number}.</p>
                        <div class="ads"><p>Buy now</p></div>
                        <script>alert("x")</script>
                    </div>
                </section>
                <div class="chapter-navigation">
                    <button id="prevBtn" data-prev-chapter-id="" data-prev-chapter-number="0" disabled></button>
                    {$next}
                </div>
            </body>
        </html>
        HTML;
}

it('follows the next chapter link across non-sequential chapter ids', function () {
    Http::fake([
        'revengernovel.com/series/knight/54/chapter-1' => Http::response(revengerChapterPage(1, 55, 2)),
        'revengernovel.com/series/knight/55/chapter-2' => Http::response(revengerChapterPage(2, 1538, 3)),
        'revengernovel.com/series/knight/1538/chapter-3' => Http::response(revengerChapterPage(3, 1539, 4)),
    ]);

    $this->artisan('novels:import-from-chapter-chain', [
        '--story-slug' => 'eternally-regressing-knight',
        '--title' => 'Eternally Regressing Knight',
        '--start-url' => 'https://revengernovel.com/series/knight/54/chapter-1',
        '--end' => 3,
        '--delay-ms' => 0,
    ])->assertExitCode(0);

    $story = Story::where('slug', 'eternally-regressing-knight')->firstOrFail();

    expect(Chapter::whereBelongsTo($story)->count())->toBe(3);

    $chapter = Chapter::whereBelongsTo($story)->where('number', 3)->firstOrFail();

    expect($chapter->title)->toBe('Chapter 3')
        ->and($chapter->source_url)->toBe('https://revengernovel.com/series/knight/1538/chapter-3')
        ->and($chapter->content)->toContain('Body of chapter 3.')
        ->and($chapter->content)->not->toContain('script')
        ->and($chapter->content)->not->toContain('Buy now')
        ->and($chapter->content)->not->toContain('onclick');
});

it('stops when the source has no next chapter link', function () {
    Http::fake([
        'revengernovel.com/series/knight/54/chapter-1' => Http::response(revengerChapterPage(1, 55, 2)),
        'revengernovel.com/series/knight/55/chapter-2' => Http::response(revengerChapterPage(2, null, null)),
    ]);

    $this->artisan('novels:import-from-chapter-chain', [
        '--story-slug' => 'eternally-regressing-knight',
        '--start-url' => 'https://revengernovel.com/series/knight/54/chapter-1',
        '--end' => 1065,
        '--delay-ms' => 0,
    ])->assertExitCode(0);

    $story = Story::where('slug', 'eternally-regressing-knight')->firstOrFail();

    expect(Chapter::whereBelongsTo($story)->count())->toBe(2);
});

it('trusts the chapter number on the page over the chapter slug in the url', function () {
    Http::fake([
        'revengernovel.com/series/knight/1538/chapter-201' => Http::response(revengerChapterPage(202, null, null)),
    ]);

    $this->artisan('novels:import-from-chapter-chain', [
        '--story-slug' => 'eternally-regressing-knight',
        '--start-url' => 'https://revengernovel.com/series/knight/1538/chapter-201',
        '--end' => 1065,
        '--delay-ms' => 0,
    ])->assertExitCode(0);

    $story = Story::where('slug', 'eternally-regressing-knight')->firstOrFail();

    expect(Chapter::whereBelongsTo($story)->where('number', 202)->exists())->toBeTrue();
});

it('does not fetch unsupported source hosts', function () {
    Http::fake();

    $this->artisan('novels:import-from-chapter-chain', [
        '--story-slug' => 'eternally-regressing-knight',
        '--start-url' => 'https://example.com/series/knight/54/chapter-1',
        '--end' => 10,
        '--delay-ms' => 0,
    ])->assertExitCode(1);

    Http::assertNothingSent();
});
