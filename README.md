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

## Importing Chapters Without a Numbered URL

Some sources address chapters by a non-sequential internal id, so the chapter number in the
URL cannot be used to walk the book. For those, the chain importer starts at one chapter URL
and follows the next-chapter id embedded in each page until it reaches `--end`.

```bash
php artisan novels:import-from-chapter-chain \
    --story-slug=eternally-regressing-knight \
    --title="Eternally Regressing Knight" \
    --cover-path=covers/eternally-regressing-knight.webp \
    --start-url="https://revengernovel.com/series/a-knight-who-eternally-regresses/54/chapter-1" \
    --end=1065
```

The chapter number comes from the page itself (the source's URL slug is unreliable and can
disagree with the chapter it serves), and the same `--dry-run`, `--only-missing`, `--force`,
`--delay-ms`, `--retries`, and `--retry-delay-ms` options are available. Because each page is
needed to find the next one, `--only-missing` skips saving rather than fetching. To resume mid
book, pass the URL of the chapter to resume from as `--start-url`.

## Supported Sources

The importer currently supports:

- **NovelFull** — `novelfull.com` and subdomains such as `www.novelfull.com`
  (`app/Services/ChapterHtmlExtractors/NovelFullChapterHtmlExtractor.php`)
- **NovelLunar** — `novellunar.com` and subdomains
  (`app/Services/ChapterHtmlExtractors/NovelLunarChapterHtmlExtractor.php`)
- **NovelTranslation** — `noveltranslation.net` and subdomains
  (`app/Services/ChapterHtmlExtractors/NovelTranslationChapterHtmlExtractor.php`)
- **RevengerNovel** — `revengernovel.com` and subdomains
  (`app/Services/ChapterHtmlExtractors/RevengerNovelChapterHtmlExtractor.php`)

Unsupported hosts are rejected before any HTTP request is sent.

The NovelFull extractor extracts the `#chapter-content` element, reads a best-effort title from `h1`, `.chapter-title`, or `.chapter h2`, and then sanitizes the HTML.

The NovelLunar extractor extracts the `article > div` element and reads a best-effort title from `h1, .chapter-title`.

The NovelTranslation extractor extracts the `.chapter-content` element and strips a leading "Chapter N" label that the source always prefixes to the body; it does not extract a title (chapter titles are set to `null`).

The RevengerNovel extractor extracts the `#contentWrapper` element and reads the title from `h1.chapter-title`. It also implements `ChapterChainNavigator`, which reports the current chapter number and the `data-next-chapter-id` of the next chapter so the chain importer can follow non-sequential ids.

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

If the source cannot be walked with a numbered URL pattern, also implement:

```text
App\Services\ChapterHtmlExtractors\ChapterChainNavigator
```

which lets `novels:import-from-chapter-chain` read the chapter number and the next chapter id out of each page.

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
