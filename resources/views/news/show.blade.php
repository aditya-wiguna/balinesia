@extends('layouts.app')

@section('title', $article->display_title . ' - Bali News')

@php
    $ogType = 'article';
    $metaDescription = str($article->display_excerpt ?? strip_tags($article->display_content ?? ''))->limit(160)->toString();
    $canonicalUrl = route('news.show', $article);
    $ogImage = $article->image_url;
@endsection

@section('content')
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center gap-2 text-sm">
                <li><a href="/" class="text-[#0A9396] hover:underline">Home</a></li>
                <li class="text-gray-400">/</li>
                <li><a href="{{ route('news.latest') }}" class="text-[#0A9396] hover:underline">News</a></li>
                @if($article->category)
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-500">{{ $article->category->name }}</li>
                @endif
            </ol>
        </nav>

        <!-- Article Header -->
        <header class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <span class="px-3 py-1 rounded-full bg-[#C9A227] text-white text-xs font-semibold">
                    {{ $article->newsSource->name }}
                </span>
                @if($article->category)
                    <span
                        class="px-3 py-1 rounded-full text-white text-xs font-semibold"
                        style="background-color: {{ $article->category->color }}"
                    >
                        {{ $article->category->name }}
                    </span>
                @endif
            </div>

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                {{ $article->display_title }}
            </h1>

            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 pb-6 border-b border-gray-200">
                @if($article->author)
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#C9A227] to-[#E85D04] flex items-center justify-center">
                            <span class="text-white text-xs font-bold">{{ substr($article->author, 0, 1) }}</span>
                        </div>
                        <span class="font-medium">{{ $article->author }}</span>
                    </div>
                @endif

                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $article->published_at?->format('F d, Y') }}
                </div>

                @if($article->is_translated)
                    <div class="flex items-center gap-1 text-[#0A9396]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        Translated to English
                    </div>
                @endif
            </div>
        </header>

        <!-- Featured Image -->
        @if($article->image_url)
            <figure class="mb-10 rounded-2xl overflow-hidden">
                <img
                    src="{{ $article->image_url }}"
                    alt="{{ $article->display_title }}"
                    class="w-full h-auto"
                >
            </figure>
        @endif

        <!-- Article Content -->
        <div class="prose prose-lg max-w-none mb-12">
            @if($article->display_content)
                <div class="text-gray-700 leading-relaxed">
                    {!! $article->display_content !!}
                </div>
            @else
                <p class="text-gray-600 italic">No content available for this article.</p>
            @endif
        </div>

        <!-- Original Link -->
        @if($article->source_url)
            <div class="bg-white rounded-xl p-6 mb-12">
                <p class="text-sm text-gray-500 mb-3">Read the original article:</p>
                <a
                    href="{{ $article->source_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 text-[#0A9396] hover:text-[#2D6A4F] font-medium"
                >
                    {{ parse_url($article->source_url, PHP_URL_HOST) }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        @endif

        <!-- Share -->
        <div class="flex items-center justify-between pb-12 border-b border-gray-200">
            <span class="text-gray-500 text-sm">Share this article</span>
            <div class="flex items-center gap-3">
                <button onclick="if(navigator.share)navigator.share({title: '{{ $article->display_title }}', url: window.location.href})" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                </button>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->display_title) }}&url={{ urlencode(request()->url()) }}" target="_blank" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                </a>
            </div>
        </div>
    </article>

    <!-- Related Articles -->
    @if($relatedArticles->isNotEmpty())
        <section class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-1 h-8 bg-gradient-to-b from-[#E85D04] to-[#C9A227] rounded-full"></div>
                    <h2 class="text-2xl font-bold text-gray-900">Related Stories</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedArticles as $related)
                        <article class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                            <a href="{{ route('news.show', $related) }}" class="group block">
                                <div class="aspect-[16/10] overflow-hidden">
                                    @if($related->image_url)
                                        <img src="{{ $related->image_url }}" alt="{{ $related->display_title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-[#C9A227]/10 to-[#E85D04]/10"></div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-gray-900 line-clamp-2 group-hover:text-[#C9A227] transition-colors">
                                        {{ $related->display_title }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-2">{{ $related->published_at?->diffForHumans() }}</p>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
