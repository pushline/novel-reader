<?php

use App\Services\ChapterHtmlSanitizer;

it('removes unsafe html while preserving chapter paragraphs', function () {
    $html = app(ChapterHtmlSanitizer::class)->sanitize(<<<'HTML'
        <p onclick="evil()">Keep <strong>this</strong>.</p>
        <div class="advertisement"><p>Remove this ad.</p></div>
        <script>alert("bad")</script>
        <iframe src="https://example.com"></iframe>
        <p><a href="javascript:alert(1)">Bad link</a></p>
    HTML);

    expect($html)->toContain('<p>Keep <strong>this</strong>.</p>')
        ->and($html)->not->toContain('onclick')
        ->and($html)->not->toContain('advertisement')
        ->and($html)->not->toContain('Remove this ad')
        ->and($html)->not->toContain('script')
        ->and($html)->not->toContain('iframe')
        ->and($html)->not->toContain('javascript:');
});

it('removes spacer-only paragraphs while preserving real paragraphs', function () {
    $html = app(ChapterHtmlSanitizer::class)->sanitize(<<<'HTML'
        <p>First paragraph.</p>
        <p>&nbsp;</p>
        <p> </p>
        <p> <br> </p>
        <p><span>&#160;</span></p>
        <p>Second paragraph.</p>
        HTML);

    expect($html)->toContain('<p>First paragraph.</p>')
        ->and($html)->toContain('<p>Second paragraph.</p>')
        ->and(substr_count($html, '<p>'))->toBe(2)
        ->and($html)->not->toContain("\u{00A0}")
        ->and($html)->not->toContain('&nbsp;')
        ->and($html)->not->toContain('<br');
});
