<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('videos')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Đã thêm danh mục "'.$data['name'].'".');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category->id));

        return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // FK restrictOnDelete: khong xoa duoc danh muc dang co video
        if ($category->videos()->withTrashed()->exists()) {
            return back()->with('error', 'Không thể xóa "'.$category->name.'" — đang có video thuộc danh mục này.');
        }

        $category->delete();

        return back()->with('success', 'Đã xóa danh mục "'.$category->name.'".');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], ['name.required' => 'Vui lòng nhập tên danh mục.']);

        // Slug sinh tu ten, dam bao unique
        $slug = Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return [...$data, 'slug' => $slug];
    }
}
