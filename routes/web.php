<?php

use App\Http\Controllers\BalineseCalendarController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\News\NewsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/search', [NewsController::class, 'search'])->name('news.search');
Route::get('/news/latest', [NewsController::class, 'latest'])->name('news.latest');
Route::get('/news/{article}', [NewsController::class, 'show'])->name('news.show');

Route::get('/jobs', [JobsController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobsController::class, 'show'])->name('jobs.show');

Route::get('/kalender-bali', [BalineseCalendarController::class, 'index'])->name('kalender-bali.index');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
