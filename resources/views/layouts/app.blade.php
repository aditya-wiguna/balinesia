<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription ?? 'Bali News Portal - Your window to Bali\'s stories, translated to English.' }}">
    <title>{{ $title ?? 'Bali News Portal' }}</title>

    <!-- Open Graph / Social -->
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:site_name" content="BaliNews">
    <meta property="og:title" content="{{ $title ?? 'Bali News Portal' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Your window to Bali\'s stories, translated to English.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Bali News Portal' }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'Your window to Bali\'s stories, translated to English.' }}">
    @if(!empty($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    <!-- Canonical -->
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 min-h-screen flex flex-col">
    <!-- Decorative top border -->
    <div class="h-1 bg-gradient-to-r from-[#0D0D0D] via-[#B22222] to-[#0D0D0D]"></div>

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="BaliNews" class="h-10 w-auto object-contain">
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="/" class="text-gray-700 hover:text-[#B22222] font-medium transition-colors">News</a>
                    <a href="{{ route('jobs.index') }}" class="text-gray-700 hover:text-[#B22222] font-medium transition-colors flex items-center gap-1">
                        Jobs
                    </a>
                    <a href="{{ route('kalender-bali.index') }}" class="text-gray-700 hover:text-[#B22222] font-medium transition-colors flex items-center gap-1">
                        Kalender
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100" aria-label="Open menu" aria-expanded="false">
                    <svg class="w-6 h-6 menu-open-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="w-6 h-6 menu-close-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="md:hidden hidden pb-4 border-t border-gray-100 pt-3">
                <nav class="flex flex-col gap-1">
                    <a href="/" class="px-4 py-3 rounded-lg text-gray-700 hover:bg-[#B22222]/5 hover:text-[#B22222] font-medium transition-colors">
                        News
                    </a>
                    <a href="{{ route('jobs.index') }}" class="px-4 py-3 rounded-lg text-gray-700 hover:bg-[#B22222]/5 hover:text-[#B22222] font-medium transition-colors">
                        Jobs
                    </a>
                    <a href="{{ route('kalender-bali.index') }}" class="px-4 py-3 rounded-lg text-gray-700 hover:bg-[#B22222]/5 hover:text-[#B22222] font-medium transition-colors">
                        Kalender
                    </a>
                </nav>
            </div>

            <!-- Mobile Search -->
            <form action="{{ route('news.search') }}" method="GET" class="md:hidden pb-4">
                <div class="relative">
                    <input
                        type="text"
                        name="q"
                        placeholder="Search news..."
                        value="{{ request('q') }}"
                        class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#B22222] text-sm"
                    >
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white text-gray-800 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- About -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="BaliNews" class="h-10 w-auto object-contain">
                    </div>
                    <p class="text-gray-500 text-sm">
                        Your window to Bali's stories, translated to English. We aggregate news from various Balinese sources and make them accessible to the world.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-semibold mb-4 text-[#0D0D0D]">Quick Links</h4>
                    <ul class="space-y-2 text-gray-500 text-sm">
                        <li><a href="/" class="hover:text-[#B22222] transition-colors">Home</a></li>
                        <li><a href="{{ route('news.latest') }}" class="hover:text-[#B22222] transition-colors">Latest News</a></li>
                        <li><a href="{{ route('jobs.index') }}" class="hover:text-[#B22222] transition-colors">Jobs in Bali</a></li>
                        <li><a href="{{ route('kalender-bali.index') }}" class="hover:text-[#B22222] transition-colors">Kalender Bali</a></li>
                    </ul>
                </div>

                <!-- Sources -->
                <div>
                    <h4 class="font-semibold mb-4 text-[#0D0D0D]">News Sources</h4>
                    <ul class="space-y-2 text-gray-500 text-sm">
                        <li>Bali News</li>
                        <li>And more coming soon...</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} BaliNews Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Decorative bottom border -->
    <div class="h-1 bg-gradient-to-r from-[#0D0D0D] via-[#B22222] to-[#0D0D0D]"></div>
</body>
</html>
