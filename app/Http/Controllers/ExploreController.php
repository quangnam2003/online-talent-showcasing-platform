<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExploreController extends Controller
{
    // FR3: tim kiem + loc the loai + sap xep/trending
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $categorySlug = $request->query('category');
        $sort = in_array($request->query('sort'), ['trending', 'new', 'views', 'rating'], true)
            ? $request->query('sort')
            : 'trending';

        $videos = Video::visible()
            ->with(['user', 'category'])
            ->search($q ?: null)
            ->when($categorySlug, fn ($query) => $query->whereHas(
                'category', fn ($c) => $c->where('slug', $categorySlug)
            ))
            ->when($sort === 'trending', fn ($query) => $query->orderByDesc('trending_score'))
            ->when($sort === 'new', fn ($query) => $query->latest())
            ->when($sort === 'views', fn ($query) => $query->orderByDesc('views'))
            ->when($sort === 'rating', fn ($query) => $query->orderByDesc('avg_rating'))
            ->paginate(12)
            ->withQueryString();

        return view('explore.index', [
            'videos' => $videos,
            'categories' => Category::orderBy('name')->get(),
            'q' => $q,
            'categorySlug' => $categorySlug,
            'sort' => $sort,
        ]);
    }
}
