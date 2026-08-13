<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Video;
use Illuminate\View\View;

class HomeController extends Controller
{
    // Trang chu = man "Discover" trong mockup: hero + trending + luoi the loai
    public function index(): View
    {
        // Video noi bat: trending_score cao nhat trong so da duyet + cong khai
        $featured = Video::visible()
            ->with(['user', 'category'])
            ->orderByDesc('trending_score')
            ->first();

        // Top 6 thinh hanh (bo qua video da lam hero)
        $trending = Video::visible()
            ->with('user')
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->id))
            ->orderByDesc('trending_score')
            ->take(6)
            ->get();

        // Luoi video moi nhat
        $videos = Video::visible()
            ->with(['user', 'category'])
            ->latest()
            ->take(8)
            ->get();

        // Cuoc thi chua ket thuc
        $contests = Contest::where('end_at', '>', now())
            ->orderBy('start_at')
            ->take(3)
            ->get();

        return view('home', compact('featured', 'trending', 'videos', 'contests'));
    }
}
