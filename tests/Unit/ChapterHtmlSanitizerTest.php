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
