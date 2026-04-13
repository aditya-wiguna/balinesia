<?php

/**
 * Advertising Banner Configuration
 *
 * Add or remove banners here. Each position can hold one active banner at a time.
 * Images should be placed in /public/images/ads/ directory.
 *
 * Available positions:
 *   home_top      — Below the hero, above latest news grid (full-width)
 *   home_mid      — Between latest news and jobs sections
 *   jobs_top      — Above the job listings
 *   jobs_mid      — Between job listings and pagination
 *   kalender_top  — Above the calendar grid
 *   sidebar       — In the sidebar (rectangle/banner shape)
 *   article_inline — Between article paragraphs (not yet wired)
 */

return [
    /*
    'home_top' => [
        'image'   => 'images/ads/your-banner.png',
        'url'     => 'https://client-site.com',
        'alt'     => 'Client Name — Your Ad Text Here',
        'weight'  => 1,  // higher = more prominent (future: for rotation)
    ],
    */

    'home_mid' => [
        'image' => '/images/ads.png',
        'url' => '#',
        'alt' => 'Advertisement',
    ],

    'jobs_top' => [
        'image' => '/images/ads.png',
        'url' => '#',
        'alt' => 'Advertisement',
    ],

    'kalender_top' => [
        'image' => '/images/ads.png',
        'url' => '#',
        'alt' => 'Advertisement',
    ],
];
