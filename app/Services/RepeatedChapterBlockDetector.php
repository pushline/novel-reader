<?php

namespace App\Services;

final class RepeatedChapterBlockDetector
{
    /**
     * @return list<array{
     *     first_start: int,
     *     first_end: int,
     *     second_start: int,
     *     second_end: int,
     *     paragraph_count: int,
     *     word_count: int,
     *     excerpt: string
     * }>
     */
    public function detect(string $html, int $minimumParagraphs = 5, int $minimumWords = 50): array
    {
        $paragraphs = $this->extractParagraphs($html);
        $normalized = array_map($this->normalize(...), $paragraphs);
        $matches = [];
        $paragraphTotal = count($paragraphs);

        for ($first = 0; $first < $paragraphTotal; $first++) {
            if ($normalized[$first] === '') {
                continue;
            }

            for ($second = $first + 1; $second < $paragraphTotal; $second++) {
                if ($normalized[$first] !== $normalized[$second]) {
                    continue;
                }

                // Only report the beginning of a repeated run, not each suffix of it.
                if ($first > 0 && $normalized[$first - 1] === $normalized[$second - 1]) {
                    continue;
                }

                $length = 0;
                $wordCount = 0;

                while (
                    $second + $length < $paragraphTotal
                    && $normalized[$first + $length] !== ''
                    && $normalized[$first + $length] === $normalized[$second + $length]
                ) {
                    $wordCount += str_word_count($paragraphs[$first + $length]);
                    $length++;
                }

                if ($length < $minimumParagraphs || $wordCount < $minimumWords) {
                    continue;
                }

                $matches[] = [
                    'first_start' => $first,
                    'first_end' => $first + $length - 1,
                    'second_start' => $second,
                    'second_end' => $second + $length - 1,
                    'paragraph_count' => $length,
                    'word_count' => $wordCount,
                    'excerpt' => mb_strimwidth($paragraphs[$first], 0, 100, '...'),
                ];
            }
        }

        return $matches;
    }

    /** @return list<string> */
    private function extractParagraphs(string $html): array
    {
        preg_match_all('/<p\b[^>]*>(.*?)<\/p>/is', $html, $matches);

        return array_map(
            fn (string $paragraph): string => trim(html_entity_decode(
                strip_tags($paragraph),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )),
            $matches[1]
        );
    }

    private function normalize(string $paragraph): string
    {
        $paragraph = mb_strtolower($paragraph);
        $paragraph = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $paragraph) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $paragraph) ?? '');
    }
}
