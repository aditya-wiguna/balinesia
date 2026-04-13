<!-- Ad Banner Component -->
{{-- Usage: <x-ad-banner position="home_top" /> --}}
{{-- slim mode removes border/shadow for inline slots: <x-ad-banner position="article_inline" slim /> --}}

@props([
    'position' => null,
    'slim' => false,
])

@if($position && ($banner = config("ads.{$position}")))
    @php
        $imagePath = $banner['image'] ?? '';
        $url       = $banner['url'] ?? '#';
        $alt       = $banner['alt'] ?? 'Advertisement';
        $exists    = $imagePath && file_exists(public_path($imagePath));
    @endphp

    @if($exists)
        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener sponsored"
            class="block {{ $slim ? '' : 'rounded-xl overflow-hidden border border-gray-200 hover:border-gray-300 transition-shadow' }} {{ $class ?? '' }}"
            aria-label="{{ $alt }}"
        >
            <img
                src="{{ asset($imagePath) }}"
                alt="{{ $alt }}"
                loading="lazy"
                decoding="async"
                class="w-full h-auto object-cover {{ $slim ? '' : 'max-h-32' }}"
            />
        </a>
    @endif
@endif
