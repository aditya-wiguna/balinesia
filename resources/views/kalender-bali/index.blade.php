@extends('layouts.app')

<?php
    $metaDescription = "Balinese Saka calendar for {$monthName} {$year}. Explore Pawukon cycles, Penanggal/Purnama/Tilem moon phases, Wuku, and sacred Balinese days.";
    $canonicalUrl = route('kalender-bali.index', ['year' => $year, 'month' => $month]);
    $ogType = 'website';
?>

@section('title', 'Kalender Bali - ' . $monthName . ' ' . $year . ' | BaliNews')
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

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-medium">Saka Calendar</span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                    Kalender Bali
                </h1>
                <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto mb-8">
                    Explore the Balinese Saka calendar with Pawukon, Penanggal, and sacred days.
                </p>

                <!-- Today's Quick Info -->
                <div class="flex flex-wrap justify-center gap-4 mt-8">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-center">
                        <div class="text-2xl font-bold">{{ $todayInfo['penanggal']['number'] }}</div>
                        <div class="text-xs text-white/70">{{ $todayInfo['penanggal']['name'] }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-center">
                        <div class="text-2xl font-bold">{{ $todayInfo['pawukon']['wuku_name'] }}</div>
                        <div class="text-xs text-white/70">Wuku</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-center">
                        <div class="text-2xl font-bold">{{ $todayInfo['saka']['sasih_name'] }}</div>
                        <div class="text-xs text-white/70">Sasih</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-center">
                        <div class="text-2xl font-bold">{{ $todayInfo['saka']['year'] }}</div>
                        <div class="text-xs text-white/70">{{ $todayInfo['saka']['year_name'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Left Sidebar: Today's Details -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Today's Balinese Calendar -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#C9A227] to-[#E85D04] p-4">
                        <h3 class="text-white font-bold">Today's Balinese Calendar</h3>
                        <p class="text-white/80 text-sm">{{ $todayInfo['gregorian_date']['formatted'] }}</p>
                    </div>
                    <div class="p-5 space-y-4">
                        <!-- Penanggal Highlight -->
                        <div class="text-center py-3 rounded-xl {{ $todayInfo['penanggal']['is_purnama'] ? 'bg-[#C9A227] text-white' : ($todayInfo['penanggal']['is_tilem'] ? 'bg-gray-900 text-white' : 'bg-[#C9A227]/10') }}">
                            <div class="text-4xl font-bold">{{ $todayInfo['penanggal']['number'] }}</div>
                            <div class="text-sm font-medium mt-1">{{ $todayInfo['penanggal']['name'] }}</div>
                            @if($todayInfo['penanggal']['is_purnama'])
                                <div class="text-xs mt-1 opacity-80"> Purnama </div>
                            @elseif($todayInfo['penanggal']['is_tilem'])
                                <div class="text-xs mt-1 opacity-80"> Tilem </div>
                            @endif
                        </div>

                        <!-- Wuku -->
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Wuku</span>
                            <span class="font-semibold text-gray-900">{{ $todayInfo['pawukon']['wuku_name'] }} ({{ $todayInfo['pawukon']['wuku'] }})</span>
                        </div>

                        <!-- Sasih -->
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Sasih</span>
                            <span class="font-semibold text-gray-900">{{ $todayInfo['saka']['sasih_name'] }}</span>
                        </div>

                        <!-- Saka Year -->
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Tahun Saka</span>
                            <span class="font-semibold text-gray-900">{{ $todayInfo['saka']['year_name'] }}</span>
                        </div>

                        <!-- Sapta Wara -->
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Saptawara</span>
                            <span class="font-semibold text-[#0A9396]">{{ $todayInfo['wewaran']['saptawara']['name'] }}</span>
                        </div>

                        <!-- Panca Wara -->
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Pancawara</span>
                            <span class="font-semibold text-[#0A9396]">{{ $todayInfo['wewaran']['pancawara']['name'] }}</span>
                        </div>

                        <!-- Zodiak -->
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-500 text-sm">Zodiak</span>
                            <span class="font-semibold text-gray-900">{{ $todayInfo['supporting']['zodiak']['name'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Wuku Reference -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#0A9396] to-[#2D6A4F] p-4">
                        <h3 class="text-white font-bold">Wuku Reference</h3>
                    </div>
                    <div class="p-4 max-h-80 overflow-y-auto">
                        <div class="space-y-1">
                            @foreach($allWuku as $wuku)
                                <div class="flex items-center justify-between py-1.5 px-2 rounded-lg {{ $wuku['no'] === $todayInfo['pawukon']['wuku'] ? 'bg-[#C9A227]/10 border border-[#C9A227]/30' : 'hover:bg-gray-50' }}">
                                    <span class="text-sm font-medium text-gray-900">{{ $wuku['name'] }}</span>
                                    <span class="text-xs text-gray-500">#{{ $wuku['no'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Calendar Grid -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Month Navigation -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex items-center justify-between p-4 border-b border-gray-100">
                        <a href="{{ route('kalender-bali.index', ['year' => $prevYear, 'month' => $prevMonth]) }}"
                           class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-gray-900">{{ $monthName }} {{ $year }}</h2>
                            <p class="text-sm text-gray-500">{{ $monthStartInfo['saka']['year_name'] }}</p>
                        </div>
                        <a href="{{ route('kalender-bali.index', ['year' => $nextYear, 'month' => $nextMonth]) }}"
                           class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="p-4">
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 text-center">
                                Pawukon Calendar Grid — 7-day week × 30 Wuku cycle
                            </p>
                        </div>

                        <!-- Day of Week Headers -->
                        <div class="grid grid-cols-7 gap-1 mb-2">
                            @foreach(['Redite', 'Coma', 'Anggara', 'Buddha', 'Wraspati', 'Sukra', 'Saniscara'] as $dayName)
                                <div class="text-center text-xs font-semibold text-gray-500 py-2 bg-gray-50 rounded-lg">
                                    {{ $dayName }}
                                </div>
                            @endforeach
                        </div>

                        <!-- Calendar Weeks -->
                        <div class="grid grid-cols-7 gap-1">
                            @foreach($monthGrid as $weekIndex => $week)
                                @foreach($week as $dayOfWeek => $cell)
                                    @if($cell)
                                        @php
                                            $isToday = $cell['day'] === (int)now()->format('j') && $month === (int)now()->format('n') && $year === (int)now()->format('Y');
                                            $isPurnama = $cell['is_purnama'] ?? false;
                                            $isTilem = $cell['is_tilem'] ?? false;
                                        @endphp
                                        <div class="relative p-2 rounded-xl min-h-[72px] border {{ $isToday ? 'border-[#C9A227] ring-1 ring-[#C9A227]/50 bg-[#C9A227]/5' : 'border-gray-100 bg-white hover:border-gray-200 hover:bg-gray-50' }}">
                                            <!-- Day number -->
                                            <div class="flex items-start justify-between">
                                                <span class="text-sm font-semibold {{ $isToday ? 'text-[#C9A227]' : 'text-gray-700' }}">
                                                    {{ $cell['day'] }}
                                                </span>
                                                @if($isPurnama)
                                                    <span class="text-[8px] font-bold text-[#C9A227] bg-[#C9A227]/10 px-1 rounded">Purnama</span>
                                                @elseif($isTilem)
                                                    <span class="text-[8px] font-bold text-gray-600 bg-gray-200 px-1 rounded">Tilem</span>
                                                @endif
                                            </div>

                                            <!-- Wuku name -->
                                            <div class="mt-1 text-[10px] text-gray-500 leading-tight">
                                                {{ $cell['wuku_name'] }}
                                            </div>

                                            <!-- Penanggal/Pangelong -->
                                            <div class="mt-1 text-[10px] font-medium {{ $cell['penanggal'] > 15 ? 'text-[#0A9396]' : 'text-[#C9A227]' }}">
                                                {{ $cell['penanggal'] }}
                                            </div>

                                            <!-- Pancawara dot -->
                                            <div class="mt-1 flex items-center gap-0.5">
                                                @php $pwc = $cell['pancawara']['no']; @endphp
                                                @for($i = 1; $i <= $pwc; $i++)
                                                    <span class="w-1 h-1 rounded-full {{ $pwc === 1 ? 'bg-[#C9A227]' : ($pwc === 2 ? 'bg-[#E85D04]' : ($pwc === 3 ? 'bg-[#0A9396]' : ($pwc === 4 ? 'bg-gray-400' : 'bg-[#2D6A4F]'))) }}"></span>
                                                @endfor
                                            </div>
                                        </div>
                                    @else
                                        <div class="rounded-xl min-h-[72px] bg-gray-50/50 border border-dashed border-gray-100"></div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="px-4 pb-4">
                        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded border border-[#C9A227] ring-1 ring-[#C9A227]/50 bg-[#C9A227]/5"></span>
                                <span>Today</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-[#C9A227]"></span>
                                <span>Purnama (Full Moon)</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-gray-400"></span>
                                <span>Tilem (New Moon)</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-[#C9A227] font-medium">●</span>
                                <span>Penanggal (1–15)</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-[#0A9396] font-medium">●</span>
                                <span>Pangelong (16–30)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wewaran Details -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#2D6A4F] to-[#0A9396] p-4">
                        <h3 class="text-white font-bold">Wewaran — Cycles of the Day</h3>
                        <p class="text-white/80 text-sm">Today's composite Balinese day signature</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Pancawara -->
                            <div class="text-center p-4 rounded-xl bg-[#C9A227]/5 border border-[#C9A227]/20">
                                <div class="text-xs text-gray-500 mb-1">Pancawara</div>
                                <div class="text-xl font-bold text-[#C9A227]">{{ $todayInfo['wewaran']['pancawara']['name'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Urip: {{ $todayInfo['wewaran']['pancawara']['urip'] }}</div>
                            </div>

                            <!-- Saptawara -->
                            <div class="text-center p-4 rounded-xl bg-[#0A9396]/5 border border-[#0A9396]/20">
                                <div class="text-xs text-gray-500 mb-1">Saptawara</div>
                                <div class="text-xl font-bold text-[#0A9396]">{{ $todayInfo['wewaran']['saptawara']['name'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Urip: {{ $todayInfo['wewaran']['saptawara']['urip'] }}</div>
                            </div>

                            <!-- Caturwara -->
                            <div class="text-center p-4 rounded-xl bg-[#2D6A4F]/5 border border-[#2D6A4F]/20">
                                <div class="text-xs text-gray-500 mb-1">Caturwara</div>
                                <div class="text-xl font-bold text-[#2D6A4F]">{{ $todayInfo['wewaran']['caturwara']['name'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Urip: {{ $todayInfo['wewaran']['caturwara']['urip'] }}</div>
                            </div>

                            <!-- Triwara -->
                            <div class="text-center p-4 rounded-xl bg-[#E85D04]/5 border border-[#E85D04]/20">
                                <div class="text-xs text-gray-500 mb-1">Triwara</div>
                                <div class="text-xl font-bold text-[#E85D04]">{{ $todayInfo['wewaran']['triwara']['name'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Urip: {{ $todayInfo['wewaran']['triwara']['urip'] }}</div>
                            </div>

                            <!-- Ekawara -->
                            <div class="text-center p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="text-xs text-gray-500 mb-1">Ekawara</div>
                                <div class="text-xl font-bold text-gray-700">{{ $todayInfo['wewaran']['ekawara']['name'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Urip: {{ $todayInfo['wewaran']['ekawara']['urip'] }}</div>
                            </div>

                            <!-- Sadwara -->
                            <div class="text-center p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="text-xs text-gray-500 mb-1">Sadwara</div>
                                <div class="text-xl font-bold text-gray-700">{{ $todayInfo['wewaran']['sadwara']['name'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Urip: {{ $todayInfo['wewaran']['sadwara']['urip'] }}</div>
                            </div>

                            <!-- Astawara -->
                            <div class="text-center p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="text-xs text-gray-500 mb-1">Astawara</div>
                                <div class="text-xl font-bold text-gray-700">{{ $todayInfo['wewaran']['astawara']['name'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Urip: {{ $todayInfo['wewaran']['astawara']['urip'] }}</div>
                            </div>

                            <!-- Dasawara -->
                            <div class="text-center p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="text-xs text-gray-500 mb-1">Dasawara</div>
                                <div class="text-xl font-bold text-gray-700">{{ $todayInfo['wewaran']['dasawara']['name'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">Urip: {{ $todayInfo['wewaran']['dasawara']['urip'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Cycles -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900">Supporting Cycles</h3>
                        <p class="text-sm text-gray-500">Ingkel, Watek, Eka Jala Rsi, Lintang, and more</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            @php
                                $cycles = [
                                    ['label' => 'Ingkel', 'data' => $todayInfo['supporting']['ingkel']],
                                    ['label' => 'Jejepan', 'data' => $todayInfo['supporting']['jejepan']],
                                    ['label' => 'Watek Alit', 'data' => $todayInfo['supporting']['watek_alit']],
                                    ['label' => 'Watek Madya', 'data' => $todayInfo['supporting']['watek_madya']],
                                    ['label' => 'Eka Jala Rsi', 'data' => $todayInfo['supporting']['eka_jala_rsi']],
                                    ['label' => 'Lintang', 'data' => $todayInfo['supporting']['lintang']],
                                    ['label' => 'Pararasan', 'data' => $todayInfo['supporting']['pararasan']],
                                    ['label' => 'Panca Sudha', 'data' => $todayInfo['supporting']['panca_sudha']],
                                    ['label' => 'Zodiak', 'data' => $todayInfo['supporting']['zodiak']],
                                ];
                            @endphp

                            @foreach($cycles as $cycle)
                                <div class="text-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                    <div class="text-xs text-gray-500 mb-1">{{ $cycle['label'] }}</div>
                                    <div class="text-sm font-bold text-gray-900">{{ $cycle['data']['name'] }}</div>
                                    <div class="text-xs text-gray-400">#{{ $cycle['data']['no'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
