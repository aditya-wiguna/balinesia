@extends('layouts.app')

@section('title', 'Bali News Portal - Latest Bali News in English')

@php
    $metaDescription = 'Discover Bali\'s stories translated to English. Stay informed with the latest news from the Island of the Gods — politics, culture, tourism, and more.';
    $canonicalUrl = route('news.index');
    $ogType = 'website';
@endsection

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-[#0A9396] via-[#2D6A4F] to-[#1a4731] text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <pattern id="batik" width="20" height="20" patternUnits="userSpaceOnUse">
                    <path d="M10 0 L20 10 L10 20 L0 10 Z" fill="currentColor"/>
                </pattern>
                <rect width="100%" height="100%" fill="url(#batik)"/>
            </svg>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                    Discover Bali's Stories
                </h1>
                <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto mb-8">
                    Your window to Bali's news, translated to English. Stay informed with stories from the Island of the Gods.
                </p>

                <!-- Category Pills -->
                <div class="flex flex-wrap justify-center gap-2">
                    @foreach($categories as $category)
                        <a href="{{ route('news.search', ['category' => $category->slug]) }}"
                           class="px-4 py-2 rounded-full bg-white/20 hover:bg-white/30 backdrop-blur-sm transition-colors text-sm font-medium">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Articles -->
    @if($featuredArticles->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-1 h-8 bg-gradient-to-b from-[#C9A227] to-[#E85D04] rounded-full"></div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Featured Stories</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach($featuredArticles as $index => $article)
                    <article class="{{ $index === 0 ? 'lg:row-span-2' : '' }}">
                        <a href="{{ route('news.show', $article) }}" class="group block h-full">
                            <div class="relative aspect-[16/9] rounded-2xl overflow-hidden bg-gray-200">
                                @if($article->image_url)
                                    <img
                                        src="{{ $article->image_url }}"
                                        alt="{{ $article->display_title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    >
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-[#C9A227]/20 to-[#E85D04]/20 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-[#C9A227]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                        </svg>
                                    </div>
                                @endif

                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                                <div class="absolute top-4 left-4">
                                    <span class="px-3 py-1 rounded-full bg-[#C9A227] text-white text-xs font-semibold">
                                        {{ $article->newsSource->name }}
                                    </span>
                                </div>

                                <div class="absolute bottom-0 left-0 right-0 p-6">
                                    <h3 class="text-white text-xl md:text-2xl font-bold mb-2 line-clamp-2 group-hover:text-[#C9A227] transition-colors">
                                        {{ $article->display_title }}
                                    </h3>
                                    @if($article->display_excerpt)
                                        <p class="text-white/80 text-sm line-clamp-2 hidden sm:block">
                                            {{ $article->display_excerpt }}
                                        </p>
                                    @endif
                                    <div class="flex items-center gap-4 mt-3 text-white/60 text-xs">
                                        @if($article->author)
                                            <span>{{ $article->author }}</span>
                                        @endif
                                        <span>{{ $article->published_at?->diffForHumans() }}</span>
                                        @if($article->is_translated)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                                </svg>
                                                EN
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Latest Articles Grid -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-1 h-8 bg-gradient-to-b from-[#0A9396] to-[#2D6A4F] rounded-full"></div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Latest News</h2>
            </div>
            <a href="{{ route('news.latest') }}" class="text-[#0A9396] hover:text-[#2D6A4F] font-medium text-sm flex items-center gap-1 transition-colors">
                View all
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($latestArticles as $article)
                <article class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
                    <a href="{{ route('news.show', $article) }}" class="group block">
                        <div class="relative aspect-[16/10] overflow-hidden">
                            @if($article->image_url)
                                <img
                                    src="{{ $article->image_url }}"
                                    alt="{{ $article->display_title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                >
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-[#C9A227]/10 to-[#E85D04]/10 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-[#C9A227]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                    </svg>
                                </div>
                            @endif

                            @if($article->category)
                                <div class="absolute top-3 right-3">
                                    <span
                                        class="px-2 py-1 rounded-full text-white text-xs font-medium"
                                        style="background-color: {{ $article->category->color }}"
                                    >
                                        {{ $article->category->name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-[#C9A227] transition-colors">
                                {{ $article->display_title }}
                            </h3>

                            @if($article->display_excerpt)
                                <p class="text-gray-600 text-sm line-clamp-2 mb-3">
                                    {{ $article->display_excerpt }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="font-medium">{{ $article->newsSource->name }}</span>
                                <span>{{ $article->published_at?->diffForHumans() }}</span>
                            </div>

                            @if($article->is_translated)
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <span class="inline-flex items-center gap-1 text-xs text-[#0A9396]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                        </svg>
                                        Translated to English
                                    </span>
                                </div>
                            @endif
                        </div>
                    </a>
                </article>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-[#C9A227]/10 flex items-center justify-center">
                        <svg class="w-12 h-12 text-[#C9A227]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No articles yet</h3>
                    <p class="text-gray-500">News articles will appear here once synced from sources.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Latest Jobs -->
    @if($latestJobs->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 bg-gradient-to-b from-[#E85D04] to-[#C9A227] rounded-full"></div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Latest Jobs</h2>
                </div>
                <a href="{{ route('jobs.index') }}" class="text-[#E85D04] hover:text-[#C9A227] font-medium text-sm flex items-center gap-1 transition-colors">
                    View all jobs
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($latestJobs as $job)
                    <article class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all overflow-hidden group">
                        <a href="{{ route('jobs.show', $job) }}" class="block p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#0A9396]/10 to-[#2D6A4F]/10 flex items-center justify-center shrink-0">
                                    <span class="text-lg font-bold text-[#0A9396]">{{ substr($job->company_name, 0, 1) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="font-bold text-gray-900 group-hover:text-[#0A9396] transition-colors line-clamp-1">
                                            {{ $job->job_title }}
                                        </h3>
                                        @if($job->is_remote)
                                            <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium shrink-0">
                                                Remote
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[#0A9396] text-sm font-medium mt-0.5">{{ $job->company_name }}</p>
                                    <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ $job->location }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $job->employment_type ?? 'Full Time' }}
                                        </span>
                                        @if($job->salary_range)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $job->salary_range }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-300 group-hover:text-[#0A9396] group-hover:translate-x-1 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-[#C9A227] via-[#E85D04] to-[#E85D04] text-white py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Stay Updated with Bali</h2>
            <p class="text-white/80 mb-8 max-w-2xl mx-auto">
                Bookmark your favorite articles, track different categories, and never miss a story from the Island of the Gods.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('news.latest') }}" class="px-8 py-3 bg-white text-[#E85D04] font-semibold rounded-full hover:bg-gray-100 transition-colors">
                    Browse All News
                </a>
                <a href="{{ route('news.search') }}" class="px-8 py-3 bg-transparent border-2 border-white text-white font-semibold rounded-full hover:bg-white/10 transition-colors">
                    Search Articles
                </a>
            </div>
        </div>
    </section>
@endsection
