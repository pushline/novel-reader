<?php

namespace App\Services\ChapterHtmlExtractors;

use App\Services\ChapterHtmlSanitizer;
use Symfony\Component\DomCrawler\Crawler;

class WebNovelChapterHtmlExtractor implements ChapterChainNavigator, ChapterHtmlExtractor
{
    private const SUPPORTED_HOSTS = [
        'webnovel.com',
        '*.webnovel.com',
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
        $chapter = $this->pageProps($html)['chapterInfo'];

        if ((int) ($chapter['vipStatus'] ?? 0) !== 0 || (int) ($chapter['price'] ?? 0) !== 0) {
            throw new \RuntimeException('The WebNovel chapter is locked or paid; stopping before importing it.');
        }

        $title = trim((string) ($chapter['chapterName'] ?? ''));
        $parts = $chapter['contents'] ?? null;

        if (! is_array($parts) || $parts === []) {
            throw new \RuntimeException('No unlocked WebNovel chapter content was found.');
        }

        $content = '';

        foreach ($parts as $index => $part) {
            $htmlPart = is_array($part) ? (string) ($part['content'] ?? '') : '';

            if ($index === 0 && trim(strip_tags($htmlPart)) === $title) {
                continue;
            }

            $content .= $htmlPart;
        }

        $content = $this->sanitizer->sanitize($content);

        if (trim(strip_tags($content)) === '') {
            throw new \RuntimeException('The unlocked WebNovel chapter content was empty after sanitization.');
        }

        return [
            'title' => $title !== '' ? $title : null,
            'content' => $content,
        ];
    }

    /**
     * @return array{
     *     number: int|null,
     *     next: array{id: string, number: int|null, url?: string}|null,
     *     skip: bool,
     *     label: string
     * }
     */
    public function chapterNavigation(string $html): array
    {
        $props = $this->pageProps($html);
        $chapter = $props['chapterInfo'];
        $book = $props['bookInfo'];
        $label = trim((string) ($chapter['chapterName'] ?? ''));
        $number = $this->chapterNumber($label);
        $nextId = trim((string) ($chapter['nextChapterId'] ?? ''));
        $bookId = trim((string) ($book['bookId'] ?? ''));

        if ($nextId === '' || $bookId === '') {
            return [
                'number' => $number,
                'next' => null,
                'skip' => $number === null,
                'label' => $label,
            ];
        }

        $nextNumber = $this->chapterNumber((string) ($chapter['nextChapterName'] ?? ''));

        return [
            'number' => $number,
            'skip' => $number === null,
            'label' => $label,
            'next' => [
                'id' => $nextId,
                'number' => $nextNumber ?? ($number !== null ? $number + 1 : null),
                'url' => "https://en.webnovel.com/book/{$bookId}/{$nextId}",
            ],
        ];
    }

    /**
     * @return array{
     *     chapterInfo: array<string, mixed>,
     *     bookInfo: array<string, mixed>
     * }
     */
    private function pageProps(string $html): array
    {
        $script = (new Crawler($html))->filter('script#__NEXT_DATA__');

        if ($script->count() === 0) {
            throw new \RuntimeException('No WebNovel __NEXT_DATA__ payload was found.');
        }

        try {
            $data = json_decode($script->text(''), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('The WebNovel chapter payload was invalid JSON.', previous: $exception);
        }

        $props = $data['props']['pageProps'] ?? null;

        if (! is_array($props) || ! is_array($props['chapterInfo'] ?? null) || ! is_array($props['bookInfo'] ?? null)) {
            throw new \RuntimeException('The WebNovel chapter payload did not contain chapter metadata.');
        }

        return [
            'chapterInfo' => $props['chapterInfo'],
            'bookInfo' => $props['bookInfo'],
        ];
    }

    private function chapterNumber(string $title): ?int
    {
        return preg_match('/\bChapter\s+(\d+)\b/i', $title, $matches) === 1
            ? (int) $matches[1]
            : null;
    }
}
