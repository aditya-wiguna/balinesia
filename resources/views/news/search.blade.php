@extends('layouts.app')

@section('title', $query ? "Search: {$query} - Bali News Portal" : 'Search News - Bali News Portal')

@php
    $metaDescription = $query
        ? "Bali news search results for \"{$query}\" — find articles, stories, and updates from the Island of the Gods."
        : 'Search Bali news articles translated to English. Find stories by keyword.';
    $canonicalUrl = route('news.search', request()->only(['q', 'category']));
    $ogType = 'website';
@endsection

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Search Header -->
        <div class="mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                @if($query)
                    Search Results for "{{ $query }}"
                @else
                    Browse All Articles
                @endif
            </h1>
            <p class="text-gray-500">
                @if($query)
                    Found {{ $articles->total() }} article(s)
                @else
                    {{ $articles->total() }} articles available
                @endif
            </p>
        </div>

        <!-- Category Filter -->
        @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-8">
                <a
                    href="{{ route('news.search') }}"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ !request('category') ? 'bg-[#C9A227] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    All
                </a>
                @foreach($categories as $category)
                    <a
                        href="{{ route('news.search', ['category' => $category->slug, 'q' => $query]) }}"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ request('category') === $category->slug ? 'bg-[#C9A227] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Search Form -->
        <form action="{{ route('news.search') }}" method="GET" class="mb-8">
            <div class="flex gap-4">
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Search for news articles..."
                        class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-[#C9A227] text-lg"
                    >
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                </div>
                <button
                    type="submit"
                    class="px-8 py-4 bg-[#C9A227] hover:bg-[#b8922a] text-white font-semibold rounded-xl transition-colors"
                >
                    Search
                </button>
            </div>
        </form>

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
                {{ $articles->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-[#C9A227]/10 flex items-center justify-center">
                    <svg class="w-12 h-12 text-[#C9A227]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No articles found</h3>
                <p class="text-gray-500 mb-6">
                    @if($query)
                        Try adjusting your search or browse categories.
                    @else
                        No articles available yet.
                    @endif
                </p>
                @if($query)
                    <a href="{{ route('news.search') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#C9A227] text-white font-medium rounded-full hover:bg-[#b8922a] transition-colors">
                        Clear Search
                    </a>
                @endif
            </div>
        @endif
    </section>
@endsection
