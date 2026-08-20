<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExploreController extends Controller
{
    /**
     * FR3: tim kiem + loc the loai + sap xep.
     * Tim theo moi thu (tieu de, mo ta, ten creator, the loai — scopeSearch);
     * kem danh sach creator co ten khop. Goi bang XHR (o tim kiem header go den dau
     * cap nhat den do) → chi tra partial ket qua, khong tra ca trang.
     */
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

        // Creator co ten khop tu khoa — hien thanh hang rieng phia tren ket qua video
        $creators = $q === '' ? collect() : User::where('role', 'creator')
            ->where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->withCount(['videos' => fn ($v) => $v->visible()])
            ->orderByDesc('followers_count')
            ->take(6)
            ->get();

        $data = [
            'videos' => $videos,
            'creators' => $creators,
            'categories' => Category::orderBy('name')->get(),
            'q' => $q,
            'categorySlug' => $categorySlug,
            'sort' => $sort,
        ];

        // Live search: chi can phan ket qua
        if ($request->ajax()) {
            return view('explore._results', $data);
        }

        return view('explore.index', $data);
    }
}
