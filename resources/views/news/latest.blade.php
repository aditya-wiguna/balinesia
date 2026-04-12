@extends('layouts.app')

@section('title', 'Latest News - Bali News Portal')

@php
    $metaDescription = 'Browse all latest Bali news articles, translated to English. Stay updated with stories from the Island of the Gods.';
    $canonicalUrl = route('news.latest');
    $ogType = 'website';
@endsection

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-10 bg-gradient-to-b from-[#0A9396] to-[#2D6A4F] rounded-full"></div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $title ?? 'Latest News' }}</h1>
            </div>
            <p class="text-gray-500">Stay updated with the newest stories from Bali</p>
        </div>

        <!-- Category Filter -->
        @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-8">
                <a
                    href="{{ route('news.latest') }}"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ !request('category') ? 'bg-[#0A9396] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    All
                </a>
                @foreach($categories as $category)
                    <a
                        href="{{ route('news.search', ['category' => $category->slug]) }}"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ request('category') === $category->slug ? 'bg-[#0A9396] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Articles Grid -->
        @if($articles->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $article)
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
                                            Translated
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $articles->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-[#0A9396]/10 flex items-center justify-center">
                    <svg class="w-12 h-12 text-[#0A9396]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No articles yet</h3>
                <p class="text-gray-500">News articles will appear here once synced from sources.</p>
            </div>
        @endif
    </section>
@endsection
