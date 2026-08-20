<?php

namespace App\Services\ChapterHtmlExtractors;

/**
 * Implemented by extractors whose source does not expose chapters through a
 * predictable numbered URL, so the importer has to walk the chapter chain and
 * read the next chapter id out of each page.
 */
interface ChapterChainNavigator
{
    /**
     * @return array{number: int|null, next: array{id: string, number: int|null}|null}
     */
    public function chapterNavigation(string $html): array;
}
