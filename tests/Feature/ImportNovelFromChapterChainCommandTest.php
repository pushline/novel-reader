<?php

use App\Models\Chapter;
use App\Models\Story;
use Illuminate\Http\Client\Request;
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

function webNovelChapterPage(int $number, string $chapterId, ?string $nextId, ?int $nextNumber, bool $locked = false): string
{
    $chapterName = "Chapter {$number} - Test title";
    $data = [
        'props' => [
            'pageProps' => [
                'chapterInfo' => [
                    'chapterId' => $chapterId,
                    'chapterName' => $chapterName,
                    'nextChapterId' => $nextId ?? '',
                    'nextChapterName' => $nextNumber !== null ? "Chapter {$nextNumber} - Next title" : '',
                    'vipStatus' => $locked ? 1 : 0,
                    'price' => $locked ? 10 : 0,
                    'contents' => [
                        ['content' => "<p>{$chapterName}</p>"],
                        ['content' => "<p onclick=\"bad()\">Body of WebNovel chapter {$number}.</p>"],
                        ['content' => '<script>alert("x")</script>'],
                    ],
                ],
                'bookInfo' => [
                    'bookId' => '33789555708924705',
                ],
            ],
        ],
    ];

    return '<html><body><script id="__NEXT_DATA__" type="application/json">'
        .json_encode($data, JSON_THROW_ON_ERROR)
        .'</script></body></html>';
}

function webNovelAnnouncementPage(string $chapterId, string $nextId, int $nextNumber): string
{
    $data = [
        'props' => [
            'pageProps' => [
                'chapterInfo' => [
                    'chapterId' => $chapterId,
                    'chapterName' => 'Happy New Year To Everyone!',
                    'nextChapterId' => $nextId,
                    'nextChapterName' => "Chapter {$nextNumber} - Real chapter",
                    'vipStatus' => 0,
                    'price' => 0,
                    'contents' => [
                        ['content' => '<p>Happy New Year To Everyone!</p>'],
                        ['content' => '<p>This is an announcement, not a chapter.</p>'],
                    ],
                ],
                'bookInfo' => [
                    'bookId' => '33789555708924705',
                ],
            ],
        ],
    ];

    return '<html><body><script id="__NEXT_DATA__" type="application/json">'
        .json_encode($data, JSON_THROW_ON_ERROR)
        .'</script></body></html>';
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

it('registers the author, genres, cover, and status on the story', function () {
    Http::fake([
        'revengernovel.com/series/knight/54/chapter-1' => Http::response(revengerChapterPage(1, null, null)),
    ]);

    $this->artisan('novels:import-from-chapter-chain', [
        '--story-slug' => 'eternally-regressing-knight',
        '--title' => 'Eternally Regressing Knight',
        '--cover-path' => 'covers/eternally-regressing-knight.webp',
        '--author' => ['SoulPung'],
        '--genre' => ['Action', 'Adventure', 'Fantasy', 'Regression'],
        '--status' => 'ongoing',
        '--start-url' => 'https://revengernovel.com/series/knight/54/chapter-1',
        '--end' => 1065,
        '--delay-ms' => 0,
    ])->assertExitCode(0);

    $story = Story::where('slug', 'eternally-regressing-knight')->firstOrFail();

    expect($story->status)->toBe('ongoing')
        ->and($story->cover_path)->toBe('covers/eternally-regressing-knight.webp')
        ->and($story->authors()->pluck('name')->all())->toBe(['SoulPung'])
        ->and($story->genres()->pluck('name')->sort()->values()->all())
        ->toBe(['Action', 'Adventure', 'Fantasy', 'Regression']);
});

it('replaces the genre list instead of appending on a second run', function () {
    Http::fake([
        'revengernovel.com/series/knight/54/chapter-1' => Http::response(revengerChapterPage(1, null, null)),
    ]);

    $options = [
        '--story-slug' => 'eternally-regressing-knight',
        '--start-url' => 'https://revengernovel.com/series/knight/54/chapter-1',
        '--end' => 1065,
        '--delay-ms' => 0,
    ];

    $this->artisan('novels:import-from-chapter-chain', $options + ['--genre' => ['Action', 'Harem']])->assertExitCode(0);
    $this->artisan('novels:import-from-chapter-chain', $options + ['--genre' => ['Action', 'Regression']])->assertExitCode(0);

    $story = Story::where('slug', 'eternally-regressing-knight')->firstOrFail();

    expect($story->genres()->pluck('name')->sort()->values()->all())->toBe(['Action', 'Regression']);
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

it('normalizes a WebNovel URL and follows chapter ids from its page data', function () {
    Http::fake([
        'en.webnovel.com/book/33789555708924705/92812975580183896' => Http::response(
            webNovelChapterPage(731, '92812975580183896', '92839290576361562', 732)
        ),
        'en.webnovel.com/book/33789555708924705/92839290576361562' => Http::response(
            webNovelChapterPage(732, '92839290576361562', '92859833002286737', 733)
        ),
    ]);

    $startUrl = 'https://www.webnovel.com/pt/book/eternally-regressing-knight_33789555708924705/'
        .'chapter-731---imperial-swordsmanship_92812975580183896';

    $this->artisan('novels:import-from-chapter-chain', [
        '--story-slug' => 'eternally-regressing-knight',
        '--start-url' => "[{$startUrl}]({$startUrl})",
        '--end' => 732,
        '--delay-ms' => 0,
        '--transport' => 'laravel',
    ])->assertExitCode(0);

    $story = Story::where('slug', 'eternally-regressing-knight')->firstOrFail();
    $chapter = Chapter::whereBelongsTo($story)->where('number', 732)->firstOrFail();

    expect(Chapter::whereBelongsTo($story)->count())->toBe(2)
        ->and($chapter->source_url)->toBe('https://en.webnovel.com/book/33789555708924705/92839290576361562')
        ->and($chapter->content)->toContain('Body of WebNovel chapter 732.')
        ->and($chapter->content)->not->toContain('onclick')
        ->and($chapter->content)->not->toContain('script');

    Http::assertSent(fn (Request $request): bool => str_starts_with($request->header('User-Agent')[0] ?? '', 'Mozilla/5.0'));
});

it('refuses to import a locked WebNovel chapter', function () {
    Http::fake([
        'en.webnovel.com/book/33789555708924705/92859833002286737' => Http::response(
            webNovelChapterPage(733, '92859833002286737', null, null, locked: true)
        ),
    ]);

    $this->artisan('novels:import-from-chapter-chain', [
        '--story-slug' => 'eternally-regressing-knight',
        '--start-url' => 'https://en.webnovel.com/book/33789555708924705/92859833002286737',
        '--end' => 733,
        '--delay-ms' => 0,
        '--transport' => 'laravel',
    ])->expectsOutputToContain('locked or paid')
        ->assertExitCode(0);

    $story = Story::where('slug', 'eternally-regressing-knight')->firstOrFail();

    expect(Chapter::whereBelongsTo($story)->exists())->toBeFalse();
});

it('skips unnumbered WebNovel announcements without consuming a chapter number', function () {
    Http::fake([
        'en.webnovel.com/book/33789555708924705/93186054156246826' => Http::response(
            webNovelChapterPage(743, '93186054156246826', '93186134552669579', 744)
        ),
        'en.webnovel.com/book/33789555708924705/93186134552669579' => Http::response(
            webNovelAnnouncementPage('93186134552669579', '93212408478236559', 744)
        ),
        'en.webnovel.com/book/33789555708924705/93212408478236559' => Http::response(
            webNovelChapterPage(744, '93212408478236559', null, null)
        ),
    ]);

    $this->artisan('novels:import-from-chapter-chain', [
        '--story-slug' => 'eternally-regressing-knight',
        '--start-url' => 'https://en.webnovel.com/book/33789555708924705/93186054156246826',
        '--end' => 744,
        '--delay-ms' => 0,
        '--transport' => 'laravel',
    ])->expectsOutput("Source entry 'Happy New Year To Everyone!': skip unnumbered content")
        ->assertExitCode(0);

    $story = Story::where('slug', 'eternally-regressing-knight')->firstOrFail();

    expect(Chapter::whereBelongsTo($story)->count())->toBe(2)
        ->and(Chapter::whereBelongsTo($story)->where('number', 744)->firstOrFail()->content)
        ->toContain('Body of WebNovel chapter 744.')
        ->not->toContain('announcement');
});
