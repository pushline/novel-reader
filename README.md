# Novel Reader

A Laravel/Livewire novel reader with an importer for chapter HTML.

## Importing Chapters

The import command reads numbered chapter URLs, extracts the readable chapter HTML, sanitizes it, and stores it in the `chapters` table.

```bash
php artisan novels:import-from-url-pattern \
    --story-slug=story \
    --title="Story" \
    --url-pattern="https://website.com/story/chapter-{chapter}.html" \
    --start=1 \
    --end=10
```

Useful options:

- `--dry-run` fetches and extracts chapters without saving them.
- `--only-missing` skips chapters already saved for the story.
- `--force` updates existing chapters even when the import hash is unchanged.
- `--delay-ms=1500` controls the delay between chapter requests.
- `--retries=5` and `--retry-delay-ms=2000` control HTTP retry behavior.

## Supported Sources

The importer currently supports:

- **NovelFull** — `novelfull.com` and subdomains such as `www.novelfull.com`
  (`app/Services/ChapterHtmlExtractors/NovelFullChapterHtmlExtractor.php`)
- **NovelLunar** — `novellunar.com` and subdomains
  (`app/Services/ChapterHtmlExtractors/NovelLunarChapterHtmlExtractor.php`)
- **NovelTranslation** — `noveltranslation.net` and subdomains
  (`app/Services/ChapterHtmlExtractors/NovelTranslationChapterHtmlExtractor.php`)

Unsupported hosts are rejected before any HTTP request is sent.

The NovelFull extractor extracts the `#chapter-content` element, reads a best-effort title from `h1`, `.chapter-title`, or `.chapter h2`, and then sanitizes the HTML. The other extractors follow the same pattern with source-specific selectors.

## Adding Another Source

To add a different website, create a new extractor that implements:

```text
App\Services\ChapterHtmlExtractors\ChapterHtmlExtractor
```

Then register it in:

```text
app/Providers/AppServiceProvider.php
```

Each extractor decides which URLs it supports through `supports(string $url)`, so every website can have its own selectors and cleanup rules.

For simple host matching, define host patterns in the extractor:

```php
private const SUPPORTED_HOSTS = [
    'example.com',
    '*.example.com',
];
```

Then delegate the check:

```php
public function supports(string $url): bool
{
    return SupportedHostPatterns::matches($url, self::SUPPORTED_HOSTS);
}
```
