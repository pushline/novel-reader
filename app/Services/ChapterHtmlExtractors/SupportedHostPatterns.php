<?php

namespace App\Services\ChapterHtmlExtractors;

class SupportedHostPatterns
{
    /**
     * @param  list<string>  $patterns
     */
    public static function matches(string $url, array $patterns): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '') {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (self::matchesPattern($host, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    private static function matchesPattern(string $host, string $pattern): bool
    {
        if (str_starts_with($pattern, '*.')) {
            $baseHost = substr($pattern, 2);

            return $host !== $baseHost && str_ends_with($host, '.'.$baseHost);
        }

        return $host === $pattern;
    }
}
