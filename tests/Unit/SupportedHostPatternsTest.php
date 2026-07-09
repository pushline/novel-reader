<?php

use App\Services\ChapterHtmlExtractors\SupportedHostPatterns;

it('matches exact and wildcard host patterns', function () {
    $patterns = [
        'novelfull.com',
        '*.novelfull.com',
    ];

    expect(SupportedHostPatterns::matches('https://novelfull.com/story/chapter-1.html', $patterns))->toBeTrue()
        ->and(SupportedHostPatterns::matches('https://www.novelfull.com/story/chapter-1.html', $patterns))->toBeTrue()
        ->and(SupportedHostPatterns::matches('https://m.novelfull.com/story/chapter-1.html', $patterns))->toBeTrue()
        ->and(SupportedHostPatterns::matches('https://novelfull.com.evil.test/story/chapter-1.html', $patterns))->toBeFalse()
        ->and(SupportedHostPatterns::matches('https://example.com/story/chapter-1.html', $patterns))->toBeFalse();
});
