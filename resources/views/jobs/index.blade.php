@extends('layouts.app')

<?php
    $metaDescription = 'Find your dream job in Bali. Browse opportunities from top companies across the Island of the Gods — updated hourly.';
    $canonicalUrl = route('jobs.index');
    $ogType = 'website';
?>

@section('title', 'Jobs in Bali - BaliJobs Portal')
@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-[#2D6A4F] via-[#0A9396] to-[#1a4731] text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <pattern id="batik" width="20" height="20" patternUnits="userSpaceOnUse">
                    <circle cx="10" cy="10" r="3" fill="currentColor"/>
                </pattern>
                <rect width="100%" height="100%" fill="url(#batik)"/>
            </svg>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                    Jobs in Bali
                </h1>
                <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto mb-8">
                    Find your dream job in Bali. Browse opportunities from top companies across the Island of the Gods.
                </p>

                <!-- Quick Stats -->
                <div class="flex flex-wrap justify-center gap-8 mt-8">
                    <div class="text-center">
                        <div class="text-3xl font-bold">{{ $jobs->total() }}</div>
                        <div class="text-white/60 text-sm">Active Jobs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold">{{ $locations->count() }}</div>
                        <div class="text-white/60 text-sm">Locations</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold">{{ $employmentTypes->count() }}</div>
                        <div class="text-white/60 text-sm">Job Types</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ad Banner: jobs_top -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-0">
        <x-ad-banner position="jobs_top" />
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Search & Filter -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 -mt-8 relative z-10">
            <form action="{{ route('jobs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="q"
                            value="{{ $query }}"
                            placeholder="Job title, company, keywords..."
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#0A9396] text-sm"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <select name="location" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#0A9396] text-sm">
                        <option value="">All Locations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}" {{ $selectedLocation == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Job Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Job Type</label>
                    <select name="type" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#0A9396] text-sm">
                        <option value="">All Types</option>
                        @foreach($employmentTypes as $type)
                            <option value="{{ $type }}" {{ $selectedType == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-3">
                    <button type="submit" class="flex-1 md:flex-none px-8 py-3 bg-[#0A9396] hover:bg-[#08787a] text-white font-semibold rounded-xl transition-colors">
                        Search Jobs
                    </button>
                    <a href="{{ route('jobs.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Job Listings -->
        @if($jobs->isNotEmpty())
            <div class="space-y-4">
                @foreach($jobs as $job)
                    <article class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group">
                        <a href="{{ route('jobs.show', $job) }}" class="block p-6">
                            <div class="flex flex-col md:flex-row md:items-start gap-4">
                                <!-- Company Logo Placeholder -->
                                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-[#0A9396]/10 to-[#2D6A4F]/10 flex items-center justify-center shrink-0">
                                    <span class="text-2xl font-bold text-[#0A9396]">{{ substr($job->company_name, 0, 1) }}</span>
                                </div>

                                <!-- Job Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900 group-hover:text-[#0A9396] transition-colors mb-1">
                                                {{ $job->job_title }}
                                            </h3>
                                            <p class="text-[#0A9396] font-medium">{{ $job->company_name }}</p>
                                        </div>

                                        @if($job->is_remote)
                                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium shrink-0">
                                                Remote
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ $job->location }}
                                        </span>

                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $job->employment_type ?? 'Full Time' }}
                                        </span>

                                        @if($job->salary_range)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $job->salary_range }}
                                            </span>
                                        @endif

                                        <span class="text-gray-400">
                                            {{ $job->posted_date?->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Arrow -->
                                <div class="hidden md:flex items-center">
                                    <svg class="w-6 h-6 text-gray-300 group-hover:text-[#0A9396] group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $jobs->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-[#0A9396]/10 flex items-center justify-center">
                    <svg class="w-12 h-12 text-[#0A9396]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No jobs found</h3>
                <p class="text-gray-500 mb-6">
                    @if($query || $selectedLocation || $selectedType)
                        Try adjusting your search or filters.
                    @else
                        Job postings will appear here once synced.
                    @endif
                </p>
                @if($query || $selectedLocation || $selectedType)
                    <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#0A9396] text-white font-medium rounded-full hover:bg-[#08787a] transition-colors">
                        Clear Filters
                    </a>
                @endif
            </div>
        @endif
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-[#2D6A4F] via-[#0A9396] to-[#2D6A4F] text-white py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Looking for Workers?</h2>
            <p class="text-white/80 mb-8 max-w-2xl mx-auto">
                Post your job openings and reach thousands of potential candidates in Bali.
            </p>
            <a href="#" class="px-8 py-3 bg-white text-[#0A9396] font-semibold rounded-full hover:bg-gray-100 transition-colors">
                Post a Job
            </a>
        </div>
    </section>
@endsection
