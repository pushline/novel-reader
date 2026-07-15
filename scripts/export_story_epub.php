<?php

declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Usage: php scripts/export_story_epub.php <story-id> <output.epub>\n");
    exit(1);
}

$storyId = filter_var($argv[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$output = $argv[2];

if ($storyId === false) {
    fwrite(STDERR, "Story ID must be a positive integer.\n");
    exit(1);
}

$database = dirname(__DIR__).'/database/database.sqlite';
$pdo = new PDO('sqlite:'.$database, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$storyQuery = $pdo->prepare('SELECT id, title, description, cover_path FROM stories WHERE id = :id');
$storyQuery->execute(['id' => $storyId]);
$story = $storyQuery->fetch(PDO::FETCH_ASSOC);

if ($story === false) {
    fwrite(STDERR, "Story {$storyId} was not found.\n");
    exit(1);
}

$authorQuery = $pdo->prepare(
    'SELECT a.name FROM authors a INNER JOIN story_author sa ON sa.author_id = a.id WHERE sa.story_id = :id ORDER BY a.name'
);
$authorQuery->execute(['id' => $storyId]);
$authors = $authorQuery->fetchAll(PDO::FETCH_COLUMN);

$chapterQuery = $pdo->prepare(
    'SELECT number, title, content FROM chapters WHERE story_id = :id ORDER BY CAST(number AS REAL), id'
);
$chapterQuery->execute(['id' => $storyId]);

$chapters = [];
while ($chapter = $chapterQuery->fetch(PDO::FETCH_ASSOC)) {
    $chapters[] = $chapter;
}

if ($chapters === []) {
    fwrite(STDERR, "Story {$storyId} has no chapters.\n");
    exit(1);
}

$directory = dirname($output);
if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
    throw new RuntimeException("Could not create output directory: {$directory}");
}

$zip = new ZipArchive;
if ($zip->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException("Could not create EPUB: {$output}");
}

$escape = static fn (?string $value): string => htmlspecialchars($value ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');
$identifier = 'urn:uuid:'.sprintf(
    '%08s-%04s-4%03s-%04x-%012s',
    dechex(random_int(0, 0xffffffff)),
    dechex(random_int(0, 0xffff)),
    dechex(random_int(0, 0xfff)),
    random_int(0x8000, 0xbfff),
    dechex(random_int(0, 0xffffffffffff))
);
$modified = gmdate('Y-m-d\TH:i:s\Z');

$zip->addFromString('mimetype', 'application/epub+zip');
$zip->setCompressionName('mimetype', ZipArchive::CM_STORE);
$zip->addFromString('META-INF/container.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
  <rootfiles><rootfile full-path="EPUB/package.opf" media-type="application/oebps-package+xml"/></rootfiles>
</container>
XML);

$css = <<<'CSS'
body { font-family: serif; line-height: 1.55; margin: 5%; }
h1 { font-size: 1.5em; margin: 0 0 1.5em; }
p { margin: 0 0 1em; }
img { height: auto; max-width: 100%; }
nav ol { list-style: none; padding-left: 0; }
nav li { margin: .35em 0; }
CSS;
$zip->addFromString('EPUB/style.css', $css);

$manifest = [
    '<item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/>',
    '<item id="css" href="style.css" media-type="text/css"/>',
];
$spine = [];
$navItems = [];

$coverPath = dirname(__DIR__).'/public/'.ltrim((string) $story['cover_path'], '/');
if (is_file($coverPath)) {
    $extension = strtolower(pathinfo($coverPath, PATHINFO_EXTENSION));
    $mediaType = match ($extension) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        default => null,
    };
    if ($mediaType !== null) {
        $coverName = 'cover.'.$extension;
        $zip->addFile($coverPath, 'EPUB/'.$coverName);
        $manifest[] = '<item id="cover" href="'.$coverName.'" media-type="'.$mediaType.'" properties="cover-image"/>';
    }
}

foreach ($chapters as $index => $chapter) {
    $sequence = $index + 1;
    $file = sprintf('chapter-%04d.xhtml', $sequence);
    $id = 'chapter-'.$sequence;
    $title = trim((string) $chapter['title']);
    if ($title === '') {
        $title = 'Chapter '.(string) $chapter['number'];
    }

    $body = preg_replace('/<\/?(?:html|head|body)[^>]*>/i', '', (string) $chapter['content']) ?? '';
    $xhtml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><head><title>'.$escape($title)
        . '</title><link rel="stylesheet" type="text/css" href="style.css"/></head><body><h1>'
        . $escape($title).'</h1>'.$body.'</body></html>';

    $zip->addFromString('EPUB/'.$file, $xhtml);
    $manifest[] = '<item id="'.$id.'" href="'.$file.'" media-type="application/xhtml+xml"/>';
    $spine[] = '<itemref idref="'.$id.'"/>';
    $navItems[] = '<li><a href="'.$file.'">'.$escape($title).'</a></li>';
}

$nav = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="en">'
    . '<head><title>Contents</title><link rel="stylesheet" type="text/css" href="style.css"/></head>'
    . '<body><nav epub:type="toc" id="toc"><h1>Contents</h1><ol>'.implode('', $navItems).'</ol></nav></body></html>';
$zip->addFromString('EPUB/nav.xhtml', $nav);

$creatorMetadata = '';
foreach ($authors as $author) {
    $creatorMetadata .= '<dc:creator>'.$escape((string) $author).'</dc:creator>';
}

$package = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="book-id">'
    . '<metadata xmlns:dc="http://purl.org/dc/elements/1.1/">'
    . '<dc:identifier id="book-id">'.$escape($identifier).'</dc:identifier>'
    . '<dc:title>'.$escape((string) $story['title']).'</dc:title>'.$creatorMetadata
    . '<dc:language>en</dc:language><dc:description>'.$escape(strip_tags((string) $story['description'])).'</dc:description>'
    . '<meta property="dcterms:modified">'.$modified.'</meta></metadata>'
    . '<manifest>'.implode('', $manifest).'</manifest><spine>'.implode('', $spine).'</spine></package>';
$zip->addFromString('EPUB/package.opf', $package);
$zip->close();

fwrite(STDOUT, sprintf("Created %s with %d chapters.\n", $output, count($chapters)));
