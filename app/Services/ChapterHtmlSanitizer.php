<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Symfony\Component\DomCrawler\Crawler;

class ChapterHtmlSanitizer
{
    public function sanitize(string $html): string
    {
        $crawler = new Crawler('<div id="novel-reader-sanitize-root">'.$html.'</div>');

        $crawler->filter('script, iframe, style, noscript, .ads, .ad, .advertisement, [class*="ads"], [class*="advert"], [id*="ads"], [id*="advert"]')
            ->each(fn (Crawler $node) => $this->removeNode($node));

        $crawler->filter('*')->each(function (Crawler $node): void {
            $domNode = $node->getNode(0);

            if (! $domNode || ! $domNode->hasAttributes()) {
                return;
            }

            $remove = [];

            foreach ($domNode->attributes as $attribute) {
                $name = strtolower($attribute->name);
                $value = strtolower($attribute->value);

                if (str_starts_with($name, 'on') || $name === 'style' || str_starts_with($value, 'javascript:')) {
                    $remove[] = $attribute->name;
                }
            }

            foreach ($remove as $attribute) {
                $domNode->removeAttribute($attribute);
            }
        });

        $root = $crawler->filter('#novel-reader-sanitize-root')->getNode(0);
        $cleaned = '';

        foreach ($root->childNodes as $child) {
            $cleaned .= $root->ownerDocument->saveHTML($child);
        }

        $config = HTMLPurifier_Config::createDefault();
        $cachePath = storage_path('framework/cache/htmlpurifier');

        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0775, true);
        }

        $config->set('HTML.Allowed', 'p,br,span,em,strong,b,i,u,h2,h3,h4,blockquote,hr,small,sup,sub,ol,ul,li,a[href|title]');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('Attr.AllowedFrameTargets', []);
        $config->set('Cache.SerializerPath', $cachePath);

        return trim((new HTMLPurifier($config))->purify($cleaned));
    }

    private function removeNode(Crawler $node): void
    {
        $domNode = $node->getNode(0);

        $domNode?->parentNode?->removeChild($domNode);
    }
}
