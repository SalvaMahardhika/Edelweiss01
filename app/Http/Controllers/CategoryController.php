<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = Category::query();

            // 1. FILTER SEARCH REALTIME (Nama atau Deskripsi)
            if ($request->filled('custom_search')) {
                $keyword = $request->custom_search;
                $categories->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            // 2. FILTER STATUS VISIBILITAS (Aktif / Non-Aktif)
            if ($request->filled('status_filter') && $request->status_filter !== 'ALL') {
                $categories->where('is_active', $request->status_filter === '1');
            }

            // 3. FILTER URUTAN TAMPILAN (Sort Order / Alphabet / Terbaru)
            if ($request->filled('sort_filter')) {
                switch ($request->sort_filter) {
                    case 'name_asc':
                        $categories->orderBy('name', 'asc');
                        break;
                    case 'latest':
                        $categories->latest();
                        break;
                    default:
                        $categories->orderBy('sort_order', 'asc');
                        break;
                }
            } else {
                $categories->orderBy('sort_order', 'asc');
            }

            return DataTables::of($categories)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return e($row->name);
                })
                ->editColumn('description', function ($row) {
                    return $row->description ? e($row->description) : '<span class="text-xs text-gray-400 italic">-</span>';
                })
                ->editColumn('is_active', function ($row) {
                    $checked = $row->is_active ? 'bg-green-500' : 'bg-red-500';
                    $translate = $row->is_active ? 'translate-x-6' : 'translate-x-1';
                    $toggleUrl = route('kategori.toggle', $row->id);

                    return '
                        <div class="flex justify-center">
                            <button type="button" onclick="toggleCategoryStatus(\''.$toggleUrl.'\')" class="relative inline-flex items-center h-6 w-12 rounded-full transition-colors duration-300 border border-white/30 '.$checked.'">
                                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-300 '.$translate.'"></span>
                            </button>
                        </div>
                    ';
                })
                ->addColumn('action', function ($row) {
                    $updateUrl = route('kategori.update', $row->id);
                    $deleteUrl = route('kategori.destroy', $row->id);
                    $nameEscaped = addslashes(e($row->name));
                    $descEscaped = addslashes(e($row->description ?? ''));

                    return '
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditModal('.$row->id.', \''.$nameEscaped.'\', \''.$descEscaped.'\', \''.$updateUrl.'\')" class="p-2 text-amber-800 hover:bg-amber-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Edit Kategori">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                            <button onclick="triggerDelete(\''.$deleteUrl.'\')" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition shadow-sm bg-white/60 border border-white" title="Hapus Kategori">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['description', 'is_active', 'action'])
                ->make(true);
        }

        $categories = Category::orderBy('sort_order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:categories,name']);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => (int) Category::max('sort_order') + 1,
            'is_active' => true,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan',
                'data' => $category,
            ]);
        }

        return back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function toggleStatus(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status kategori diperbarui',
                'is_active' => $category->is_active,
            ]);
        }

        return back()->with('success', 'Status kategori diperbarui');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:categories,name,'.$id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diupdate',
                'data' => $category,
            ]);
        }

        return back()->with('success', 'Kategori diupdate');
    }

    public function destroy(Request $request, $id)
    {
        Category::findOrFail($id)->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus',
            ]);
        }

        return back()->with('success', 'Kategori dihapus');
    }
}