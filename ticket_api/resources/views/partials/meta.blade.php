{{-- resources/views/partials/meta.blade.php --}}
<!-- Dynamic Meta Tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $seo['title'] ?? $siteSettings['site_name'] ?? 'Ferry Ticket System' }}</title>

<!-- SEO Meta Tags -->
<meta name="description" content="{{ $seo['description'] ?? $siteSettings['meta_description'] ?? 'Book your ferry tickets online for a seamless travel experience.' }}">
<meta name="keywords" content="{{ $seo['keywords'] ?? $siteSettings['meta_keywords'] ?? 'ferry tickets, sea travel, online booking' }}">
<meta name="author" content="{{ $siteSettings['site_name'] ?? 'Ferry Ticket System' }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? $siteSettings['site_name'] ?? 'Ferry Ticket System' }}">
<meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? $siteSettings['meta_description'] ?? 'Book your ferry tickets online for a seamless travel experience.' }}">
<meta property="og:image" content="{{ $seo['og_image'] ?? $siteSettings['og_image'] ?? asset('images/og-image.jpg') }}">

<!-- Twitter -->
<meta property="twitter:card" content="{{ $seo['twitter_card'] ?? 'summary_large_image' }}">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? $siteSettings['site_name'] ?? 'Ferry Ticket System' }}">
<meta property="twitter:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? $siteSettings['meta_description'] ?? 'Book your ferry tickets online for a seamless travel experience.' }}">
<meta property="twitter:image" content="{{ $seo['og_image'] ?? $siteSettings['og_image'] ?? asset('images/og-image.jpg') }}">

<!-- Canonical URL -->
<link rel="canonical" href="{{ url()->current() }}">

<!-- Favicon -->
<link rel="icon" href="{{ $siteSettings['favicon'] ?? asset('favicon.ico') }}" type="image/x-icon">
<link rel="shortcut icon" href="{{ $siteSettings['favicon'] ?? asset('favicon.ico') }}" type="image/x-icon">
