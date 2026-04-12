<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $featuredArticles = Article::with(['newsSource', 'englishTranslation'])
            ->approved()
            ->published()
            ->featured()
            ->latest('published_at')
            ->take(3)
            ->get();

        $latestArticles = Article::with(['newsSource', 'englishTranslation'])
            ->approved()
            ->published()
            ->latest('published_at')
            ->take(9)
            ->get();

        $categories = Category::active()->get();

        $latestJobs = JobPosting::approved()
            ->active()
            ->notExpired()
            ->latest('posted_date')
            ->take(4)
            ->get();

        return view('news.index', [
            'featuredArticles' => $featuredArticles,
            'latestArticles' => $latestArticles,
            'categories' => $categories,
            'latestJobs' => $latestJobs,
        ]);
    }

    public function search(Request $request): View
    {
        $query = $request->get('q', '');
        $categorySlug = $request->get('category');

        $articles = Article::with(['newsSource', 'englishTranslation', 'category'])
            ->approved()
            ->published()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%")
                        ->orWhereHas('englishTranslation', function ($q) use ($query) {
                            $q->where('title', 'like', "%{$query}%")
                                ->orWhere('content', 'like', "%{$query}%");
                        });
                });
            })
            ->when($categorySlug, function ($q) use ($categorySlug) {
                $q->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::active()->get();

        return view('news.search', [
            'articles' => $articles,
            'categories' => $categories,
            'query' => $query,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->is_approved && $article->published_at, 404);

        $article->load(['newsSource', 'englishTranslation', 'category']);

        $relatedArticles = Article::with(['newsSource', 'englishTranslation'])
            ->approved()
            ->published()
            ->where('id', '!=', $article->id)
            ->when($article->category_id, function ($q) use ($article) {
                $q->where('category_id', $article->category_id);
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('news.show', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    public function latest(): View
    {
        $articles = Article::with(['newsSource', 'englishTranslation'])
            ->approved()
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $categories = Category::active()->get();

        return view('news.latest', [
            'articles' => $articles,
            'categories' => $categories,
            'title' => 'Latest News',
        ]);
    }
}
