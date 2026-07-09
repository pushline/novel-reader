<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<meta name="description" content="A dark, focused home for long-form stories.">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Novel Reader') : config('app.name', 'Novel Reader') }}">
<meta property="og:description" content="A dark, focused home for long-form stories.">
<meta property="og:image" content="{{ url('/brand/novel-reader-social.png') }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Novel Reader') : config('app.name', 'Novel Reader') }}">
<meta name="twitter:description" content="A dark, focused home for long-form stories.">
<meta name="twitter:image" content="{{ url('/brand/novel-reader-social.png') }}">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
