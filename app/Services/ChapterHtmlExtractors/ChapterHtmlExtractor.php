<?php

namespace App\Services\ChapterHtmlExtractors;

interface ChapterHtmlExtractor
{
    public function supports(string $url): bool;

    /**
     * @return array{title: string|null, content: string}
     */
    public function extract(string $html): array;
}
