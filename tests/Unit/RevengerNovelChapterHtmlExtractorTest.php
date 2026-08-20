<?php

use App\Services\ChapterHtmlExtractors\RevengerNovelChapterHtmlExtractor;
use App\Services\ChapterHtmlSanitizer;

function revengerExtractor(): RevengerNovelChapterHtmlExtractor
{
    return new RevengerNovelChapterHtmlExtractor(new ChapterHtmlSanitizer);
}

it('supports revengernovel hosts only', function () {
    $extractor = revengerExtractor();

    expect($extractor->supports('https://revengernovel.com/series/knight/54/chapter-1'))->toBeTrue()
        ->and($extractor->supports('https://www.revengernovel.com/series/knight/54/chapter-1'))->toBeTrue()
        ->and($extractor->supports('https://example.com/series/knight/54/chapter-1'))->toBeFalse();
});

it('reads the chapter number and the next chapter id from the navigation', function () {
    $html = <<<'HTML'
        <h1 class="chapter-title">Chapter 202 : </h1>
        <div class="chapter-navigation">
            <button id="prevBtn" data-prev-chapter-id="1537" data-prev-chapter-number="201"></button>
            <button id="nextBtn" data-next-chapter-id="1539" data-next-chapter-number="203"></button>
        </div>
        HTML;

    expect(revengerExtractor()->chapterNavigation($html))->toBe([
        'number' => 202,
        'next' => ['id' => '1539', 'number' => 203],
    ]);
});

it('reports no next chapter on the last chapter', function () {
    $html = <<<'HTML'
        <h1 class="chapter-title">Chapter 1065 : </h1>
        <div class="chapter-navigation">
            <button id="prevBtn" data-prev-chapter-id="1537" data-prev-chapter-number="1064"></button>
            <button id="nextBtn" data-next-chapter-id="" data-next-chapter-number="0" disabled></button>
        </div>
        HTML;

    expect(revengerExtractor()->chapterNavigation($html)['next'])->toBeNull();
});

it('falls back to the navigation numbers when the title has no number', function () {
    $html = <<<'HTML'
        <h1 class="chapter-title">Prologue</h1>
        <button id="prevBtn" data-prev-chapter-id="" data-prev-chapter-number="0" disabled></button>
        <button id="nextBtn" data-next-chapter-id="55" data-next-chapter-number="2"></button>
        HTML;

    expect(revengerExtractor()->chapterNavigation($html)['number'])->toBe(1);
});

it('extracts and sanitizes the content wrapper', function () {
    $html = <<<'HTML'
        <section id="chapterContent" class="chapter-content">
            <div id="contentWrapper">
                <p style="color:red">First line.</p>
                <script>alert("x")</script>
            </div>
        </section>
        HTML;

    $result = revengerExtractor()->extract($html);

    expect($result['content'])->toContain('First line.')
        ->and($result['content'])->not->toContain('script')
        ->and($result['content'])->not->toContain('style');
});

it('fails when the chapter body is missing', function () {
    revengerExtractor()->extract('<html><body><p>nothing</p></body></html>');
})->throws(RuntimeException::class);
