<?php

namespace App\Services\ChapterHtmlExtractors;

use App\Services\ChapterHtmlSanitizer;
use Symfony\Component\DomCrawler\Crawler;

class NovelFullChapterHtmlExtractor implements ChapterHtmlExtractor
{
    private const SUPPORTED_HOSTS = [
        'novelfull.com',
        '*.novelfull.com',
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
        $content = $crawler->filter('#chapter-content');

        if ($content->count() === 0) {
            throw new \RuntimeException('No #chapter-content element was found.');
        }

        $rawContent = '';
        $contentNode = $content->getNode(0);

        foreach ($contentNode->childNodes as $child) {
            $rawContent .= $contentNode->ownerDocument->saveHTML($child);
        }

        $title = $this->firstText($crawler, 'h1, .chapter-title, .chapter h2');

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
