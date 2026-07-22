<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:categories']);
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => Category::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        return back()->with('success', 'Status kategori diperbarui');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->only(['name', 'description']));

        return back()->with('success', 'Kategori diupdate');
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return back()->with('success', 'Kategori dihapus');
    }
}
