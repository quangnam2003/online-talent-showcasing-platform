<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Chip the loai tren header (layout dung o moi trang)
        View::composer('layouts.app', function ($view) {
            try {
                $navCategories = Category::orderBy('name')->take(4)->get();
            } catch (\Throwable $e) {
                $navCategories = collect(); // DB chua migrate thi bo qua
            }
            $view->with('navCategories', $navCategories);
        });
    }
}
