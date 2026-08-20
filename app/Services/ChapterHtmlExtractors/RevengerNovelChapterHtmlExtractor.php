<?php

namespace App\Services\ChapterHtmlExtractors;

use App\Services\ChapterHtmlSanitizer;
use Symfony\Component\DomCrawler\Crawler;

class RevengerNovelChapterHtmlExtractor implements ChapterChainNavigator, ChapterHtmlExtractor
{
    private const SUPPORTED_HOSTS = [
        'revengernovel.com',
        '*.revengernovel.com',
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
        $content = $crawler->filter('#contentWrapper');

        if ($content->count() === 0) {
            $content = $crawler->filter('#chapterContent, .chapter-content');
        }

        if ($content->count() === 0) {
            throw new \RuntimeException('No #contentWrapper element was found.');
        }

        $rawContent = '';
        $contentNode = $content->getNode(0);

        foreach ($contentNode->childNodes as $child) {
            $rawContent .= $contentNode->ownerDocument->saveHTML($child);
        }

        return [
            'title' => $this->firstText($crawler, 'h1.chapter-title, h1'),
            'content' => $this->sanitizer->sanitize($rawContent),
        ];
    }

    /**
     * Chapter ids are not sequential and the URL slug is unreliable, so both the
     * chapter number and the next chapter id come from the page itself.
     *
     * @return array{number: int|null, next: array{id: string, number: int|null}|null}
     */
    public function chapterNavigation(string $html): array
    {
        $crawler = new Crawler($html);

        $nextId = $this->attribute($crawler, '#nextBtn', 'data-next-chapter-id');
        $nextNumber = $this->intAttribute($crawler, '#nextBtn', 'data-next-chapter-number');
        $previousNumber = $this->intAttribute($crawler, '#prevBtn', 'data-prev-chapter-number');

        return [
            'number' => $this->currentNumber($crawler, $nextNumber, $previousNumber),
            'next' => $nextId !== null
                ? ['id' => $nextId, 'number' => $nextNumber]
                : null,
        ];
    }

    private function currentNumber(Crawler $crawler, ?int $nextNumber, ?int $previousNumber): ?int
    {
        $title = $this->firstText($crawler, 'h1.chapter-title');

        if ($title !== null && preg_match('/chapter\s+(\d+)/i', $title, $matches) === 1) {
            return (int) $matches[1];
        }

        if ($nextNumber !== null && $nextNumber > 1) {
            return $nextNumber - 1;
        }

        if ($previousNumber !== null && $previousNumber > 0) {
            return $previousNumber + 1;
        }

        return null;
    }

    private function attribute(Crawler $crawler, string $selector, string $attribute): ?string
    {
        $match = $crawler->filter($selector);

        if ($match->count() === 0) {
            return null;
        }

        $value = trim((string) $match->first()->attr($attribute));

        return $value !== '' ? $value : null;
    }

    private function intAttribute(Crawler $crawler, string $selector, string $attribute): ?int
    {
        $value = $this->attribute($crawler, $selector, $attribute);

        return $value !== null && ctype_digit($value) ? (int) $value : null;
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
