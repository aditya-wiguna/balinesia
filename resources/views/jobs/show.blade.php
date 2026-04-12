@extends('layouts.app')

<?php
    $ogType = 'article';
    $metaDescription = trim(strip_tags($job->description ?? $job->job_title));
    if (strlen($metaDescription) > 160) {
        $metaDescription = substr($metaDescription, 0, 157) . '...';
    }
    $canonicalUrl = route('jobs.show', $job);
?>

@section('title', $job->job_title . ' - ' . $job->company_name . ' - BaliJobs')
@section('content')
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center gap-2 text-sm">
                <li><a href="{{ route('jobs.index') }}" class="text-[#0A9396] hover:underline">Jobs</a></li>
                <li class="text-gray-400">/</li>
                <li><a href="{{ route('jobs.index', ['location' => $job->location]) }}" class="text-[#0A9396] hover:underline">{{ $job->location }}</a></li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-500 truncate">{{ $job->job_title }}</li>
            </ol>
        </nav>

        <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Header -->
            <header class="p-8 border-b border-gray-100">
                <div class="flex flex-col md:flex-row md:items-start gap-6">
                    <!-- Company Logo -->
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#0A9396]/10 to-[#2D6A4F]/10 flex items-center justify-center shrink-0">
                        <span class="text-3xl font-bold text-[#0A9396]">{{ substr($job->company_name, 0, 1) }}</span>
                    </div>

                    <div class="flex-1">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                            {{ $job->job_title }}
                        </h1>
                        <p class="text-xl text-[#0A9396] font-medium mb-4">{{ $job->company_name }}</p>

                        <div class="flex flex-wrap items-center gap-3">
                            @if($job->is_remote)
                                <span class="px-4 py-1.5 rounded-full bg-green-100 text-green-700 text-sm font-medium">
                                    Remote Friendly
                                </span>
                            @endif
                            @if($job->employment_type)
                                <span class="px-4 py-1.5 rounded-full bg-[#0A9396]/10 text-[#0A9396] text-sm font-medium">
                                    {{ $job->employment_type }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#0A9396]/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#0A9396]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Location</p>
                            <p class="font-medium text-gray-900">{{ $job->location }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#0A9396]/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#0A9396]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Posted</p>
                            <p class="font-medium text-gray-900">{{ $job->posted_date?->diffForHumans() }}</p>
                        </div>
                    </div>

                    @if($job->salary_range)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-[#0A9396]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#0A9396]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Salary</p>
                                <p class="font-medium text-gray-900">{{ $job->salary_range }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#0A9396]/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#0A9396]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Source</p>
                            <p class="font-medium text-gray-900">{{ $job->source_name }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Apply Button -->
            <div class="p-6 bg-gray-50">
                <a
                    href="{{ $job->source_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block w-full py-4 bg-gradient-to-r from-[#0A9396] to-[#2D6A4F] hover:from-[#08787a] hover:to-[#1a4731] text-white font-semibold text-center rounded-xl transition-all shadow-lg hover:shadow-xl"
                >
                    Apply Now on {{ $job->source_name }}
                    <svg class="inline w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>

            <!-- Description -->
            @if($job->description)
                <div class="p-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Job Description</h2>
                    <div class="prose prose-gray max-w-none text-gray-600">
                        {!! nl2br(e($job->description)) !!}
                    </div>
                </div>
            @endif

            <!-- Requirements -->
            @if($job->requirements)
                <div class="p-8 border-t border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Requirements</h2>
                    <div class="prose prose-gray max-w-none text-gray-600">
                        {!! nl2br(e($job->requirements)) !!}
                    </div>
                </div>
            @endif

            <!-- Footer -->
            <footer class="px-8 py-6 bg-gray-50 border-t border-gray-100">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="text-sm text-gray-500">
                        @if($job->expires_date)
                            <p>Expires: {{ $job->expires_date->format('M d, Y') }}</p>
                        @endif
                        <p class="mt-1">Source: <a href="{{ $job->source_url }}" target="_blank" class="text-[#0A9396] hover:underline">{{ parse_url($job->source_url, PHP_URL_HOST) }}</a></p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button onclick="if(navigator.share)navigator.share({title: '{{ $job->job_title }} at {{ $job->company_name }}', url: window.location.href})" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </button>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($job->job_title . ' at ' . $job->company_name) }}&url={{ urlencode(request()->url()) }}" target="_blank" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </footer>
        </article>
    </section>

    <!-- Related Jobs -->
    @if($relatedJobs->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-1 h-8 bg-gradient-to-b from-[#0A9396] to-[#2D6A4F] rounded-full"></div>
                <h2 class="text-2xl font-bold text-gray-900">Similar Jobs</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($relatedJobs as $related)
                    <article class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
                        <a href="{{ route('jobs.show', $related) }}" class="block p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-lg bg-[#0A9396]/10 flex items-center justify-center shrink-0">
                                    <span class="text-lg font-bold text-[#0A9396]">{{ substr($related->company_name, 0, 1) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 truncate group-hover:text-[#0A9396] transition-colors">
                                        {{ $related->job_title }}
                                    </h3>
                                    <p class="text-[#0A9396] text-sm">{{ $related->company_name }}</p>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                        <span>{{ $related->location }}</span>
                                        <span>•</span>
                                        <span>{{ $related->posted_date?->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
