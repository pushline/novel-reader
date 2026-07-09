<?php

namespace App\Services\ChapterHtmlExtractors;

use App\Services\ChapterHtmlSanitizer;
use Symfony\Component\DomCrawler\Crawler;

class NovelLunarChapterHtmlExtractor implements ChapterHtmlExtractor
{
    private const SUPPORTED_HOSTS = [
        'novellunar.com',
        '*.novellunar.com',
    ];

    public function __construct(private readonly ChapterHtmlSanitizer $sanitizer) {}

    public function supports(string $url): bool
    {
        return SupportedHostPatterns::matches($url, self::SUPPORTED_HOSTS);
    }

    /**
     * @return array{title: string|null, content: string}
     */
    public function extract(string $html): array
    {
        $crawler = new Crawler($html);
        $content = $crawler->filter('article > div');

        if ($content->count() === 0) {
            throw new \RuntimeException('No article content element was found.');
        }

        $rawContent = '';
        $contentNode = $content->getNode(0);

        foreach ($contentNode->childNodes as $child) {
            $rawContent .= $contentNode->ownerDocument->saveHTML($child);
        }

        $title = $this->firstText($crawler, 'h1, .chapter-title');

        return [
            'title' => $title,
            'content' => $this->sanitizer->sanitize($rawContent),
        ];
    }

    private function firstText(Crawler $crawler, string $selector): ?string
    {
        $match = $crawler->filter($selector);

        if ($match->count() === 0) {
            return null;
        }

        $text = trim($match->first()->text(''));

        return $text !== '' ? $text : null;
    }
}
