<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // 1. HITUNG STATISTIK UNTUK CARDS
        $totalCategories = \App\Models\Category::count();
        $totalBooks = \App\Models\Book::sum('stock');
        
        // Asumsi ada kolom 'status' di tabel categories (Aktif/Nonaktif)
        $activeCategories = \App\Models\Category::where('status', 'Aktif')->count();
        $inactiveCategories = \App\Models\Category::where('status', 'Nonaktif')->count();

        // 2. QUERY UTAMA DENGAN FILTER & SORTING
        $query = \App\Models\Category::withCount('books');

        // Filter Nama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sort = $request->get('sort', 'az');
        if ($sort === 'az') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'za') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'newest') {
            $query->orderBy('created_at', 'desc');
        }

        $categories = $query->paginate(10)->appends($request->all());

        // Data untuk sidebar (Kategori Terpopuler)
        $topCategories = \App\Models\Category::withCount('books')
            ->orderBy('books_count', 'desc')
            ->take(5)
            ->get();

        return view('categories.index', compact(
            'categories', 'totalCategories', 'totalBooks', 
            'activeCategories', 'inactiveCategories', 'topCategories'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $validated['status'] = 'Aktif';
        $validated['quota_group'] = Category::guessQuotaGroup($validated['name']);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Data kategori berhasil ditambahkan!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'status' => 'required|string|in:Aktif,Nonaktif',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Data kategori berhasil diperbarui!');
    }

    public function destroy(Category $category)
    {
        // Cek apakah ada buku yang menggunakan kategori ini
        if ($category->books()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal hapus! Kategori ini masih digunakan oleh buku.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus!');
    }
}