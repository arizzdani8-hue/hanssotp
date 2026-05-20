<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Page;
use App\Models\Popup;
use App\Models\Setting;
use App\Models\Slider;

class PageController extends Controller
{
    public function home()
    {
        $sliders = Slider::where('is_active', true)->orderBy('sort_order')->get();
        $popups = Popup::where('is_active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->get();

        return view('pages.home', compact('sliders', 'popups'));
    }

    public function articles()
    {
        $articles = Article::published()->latest('published_at')->paginate(12);
        return view('pages.articles', compact('articles'));
    }

    public function article(string $slug)
    {
        $article = Article::where('slug', $slug)->published()->firstOrFail();
        return view('pages.article', compact('article'));
    }

    public function page(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return view('pages.page', compact('page'));
    }

    public function apiDocs()
    {
        return view('pages.api-docs');
    }
}
