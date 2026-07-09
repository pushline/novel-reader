<?php

namespace App\Services\ChapterHtmlExtractors;

use App\Services\ChapterHtmlSanitizer;
use Symfony\Component\DomCrawler\Crawler;

class NovelTranslationChapterHtmlExtractor implements ChapterHtmlExtractor
{
    private const SUPPORTED_HOSTS = [
        'noveltranslation.net',
        '*.noveltranslation.net',
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
        $content = $crawler->filter('.chapter-content');

        if ($content->count() === 0) {
            throw new \RuntimeException('No .chapter-content element was found.');
        }

        $contentNode = $content->getNode(0);
        $this->stripLeadingChapterLabel($contentNode);

        $rawContent = '';

        foreach ($contentNode->childNodes as $child) {
            $rawContent .= $contentNode->ownerDocument->saveHTML($child);
        }

        return [
            'title' => null,
            'content' => $this->sanitizer->sanitize($rawContent),
        ];
    }

    /**
     * The source always prefixes the chapter body with a "Chapter N" label
     * (either as its own leading text or as the first element's text).
     */
    private function stripLeadingChapterLabel(\DOMNode $container): void
    {
        foreach (iterator_to_array($container->childNodes) as $child) {
            $text = trim($child->textContent ?? '');

            if ($text === '') {
                continue;
            }

            if ($child->nodeType === XML_TEXT_NODE) {
                $child->nodeValue = preg_replace('/^\s*Chapter\s+\d+\s*/i', '', $child->nodeValue, 1);
            } elseif (preg_match('/^Chapter\s+\d+$/i', $text)) {
                $child->parentNode->removeChild($child);
            }

            break;
        }
    }
}
