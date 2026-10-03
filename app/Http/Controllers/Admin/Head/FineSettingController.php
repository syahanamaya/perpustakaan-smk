<?php

namespace App\Http\Controllers\Admin\Head;

use App\Http\Controllers\Controller;
use App\Models\FineSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FineSettingController extends Controller
{
    public function index()
    {
        // Ambil aturan yang sedang aktif saat ini
        $currentSetting = FineSetting::query()->where('is_active', true)->first();
        
        // Ambil semua riwayat perubahan
        $histories = FineSetting::query()->with('creator')->orderBy('created_at', 'desc')->get();

        return view('admin.head.fines.index', compact('currentSetting', 'histories'));
    }

    public function store(Request $request)
    {
        // 1. Validasi input dengan pesan kustom
        $validated = $request->validate([
            'late_fee_per_day'        => 'required|numeric|min:0',
            'max_fee_per_book'        => 'required|numeric|min:0',
            'tolerance_days'          => 'required|numeric|min:0',
            'damaged_book_fee'        => 'nullable|numeric|min:0',
            'damage_light_percent'    => 'required|numeric|min:0|max:100',
            'damage_medium_percent'   => 'required|numeric|min:0|max:100',
            'damage_heavy_percent'    => 'required|numeric|min:0|max:100',
            'lost_book_fee_type'      => 'nullable|string',
            'rounding_rule'           => 'required|string|in:Dibulatkan ke atas (Ribuan),Tidak dibulatkan',
            'max_fee_per_transaction' => 'required|numeric|min:0',
        ], [
            '*.required' => 'Kolom ini wajib diisi dan tidak boleh kosong.',
            '*.numeric'  => 'Kolom ini harus berupa angka.',
            '*.min'      => 'Nominal denda atau hari tidak boleh bernilai negatif.',
        ]);

        // 2. Nonaktifkan semua aturan yang lama
        FineSetting::query()->where('is_active', true)->update(['is_active' => false]);

        // 3. Buat aturan baru dan jadikan aktif menggunakan data yang sudah divalidasi
        FineSetting::query()->create(array_merge($validated, [
            'is_active'  => true,
            'created_by' => Auth::id(),
            'lost_book_fee_type' => 'Sesuai Harga Buku',
            'damaged_book_fee' => 0,
        ]));

        return redirect()->back()->with('success', 'Aturan denda berhasil diperbarui!');
    }
}