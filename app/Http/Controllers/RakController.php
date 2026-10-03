<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rak;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class RakController extends Controller
{
    public function index(Request $request)
    {
        // 1. DATA STATISTIK UNTUK CARD ATAS
        $total_rak = Rak::count();
        $total_buku = Book::sum('stock');
        
        // Rak Terisi (asumsi rak terisi jika ada buku di dalamnya)
        $rak_terisi_count = Rak::has('books')->count();
        $rak_kosong_count = $total_rak - $rak_terisi_count;

        // 2. QUERY DENGAN FILTER
        $query = Rak::withCount(['books as total_buku' => function($q) {
            $q->select(DB::raw('sum(stock)'));
        }]);

        if ($request->filled('search')) {
            $query->where('nama_rak', 'like', "%{$request->search}%")
                  ->orWhere('kode_rak', 'like', "%{$request->search}%");
        }

        if ($request->filled('lokasi') && $request->lokasi != 'Semua Lokasi') {
            $query->where('lokasi', $request->lokasi);
        }

        $raks = $query->paginate(10)->appends($request->all());

        // Data untuk Chart di sidebar
        $distribusi_lokasi = Rak::select('lokasi', DB::raw('count(*) as total'))
                                ->groupBy('lokasi')->get();

        return view('raks.index', compact(
            'raks', 'total_rak', 'total_buku', 'rak_terisi_count', 
            'rak_kosong_count', 'distribusi_lokasi'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_rak'  => 'required|string|unique:raks,kode_rak',
            'nama_rak'  => 'required|string|max:255',
            'lokasi'    => 'required|string',
            'kapasitas' => 'required|integer|min:1',
        ]);

        Rak::create($validated);

        return redirect()->route('admin.raks.index')->with('success', 'Rak baru berhasil ditambahkan!');
    }

    public function update(Request $request, Rak $rak)
    {
        $validated = $request->validate([
            'kode_rak'  => 'required|string|unique:raks,kode_rak,' . $rak->id,
            'nama_rak'  => 'required|string|max:255',
            'lokasi'    => 'required|string',
            'kapasitas' => 'required|integer|min:1',
        ]);

        $rak->update($validated);

        return redirect()->route('admin.raks.index')->with('success', 'Data rak berhasil diperbarui!');
    }

    public function destroy(Rak $rak)
    {
        // Cek apakah rak masih ada bukunya
        if ($rak->books()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal menghapus! Rak masih berisi buku.');
        }

        $rak->delete();
        return redirect()->route('admin.raks.index')->with('success', 'Rak berhasil dihapus!');
    }
}