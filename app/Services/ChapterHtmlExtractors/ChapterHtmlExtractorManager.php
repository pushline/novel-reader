<?php

namespace App\Services\ChapterHtmlExtractors;

class ChapterHtmlExtractorManager
{
    /**
     * @param  iterable<ChapterHtmlExtractor>  $extractors
     */
    public function __construct(private readonly iterable $extractors) {}

    public function supports(string $url): bool
    {
        return $this->extractorFor($url) !== null;
    }

    public function extractorFor(string $url): ?ChapterHtmlExtractor
    {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($url)) {
                return $extractor;
            }
        }

        return null;
    }

    /**
     * @return array{title: string|null, content: string}
     */
    public function extract(string $url, string $html): array
    {
        $extractor = $this->extractorFor($url);

        if ($extractor !== null) {
            return $extractor->extract($html);
        }

        $host = parse_url($url, PHP_URL_HOST) ?: 'unknown host';

        throw new \RuntimeException("No chapter HTML extractor is configured for {$host}.");
    }
}
