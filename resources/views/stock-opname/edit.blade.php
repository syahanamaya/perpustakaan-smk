<x-layouts.app title="Perbaiki Pengajuan Opname">
    <div x-data="{
        stokSistem: {{ $stockOpname->stok_sistem }},
        stokDipinjam: {{ $stockOpname->stok_dipinjam }},
        stokMaster: {{ $stockOpname->stok_master }},
        hilangSistem: {{ $stockOpname->jumlah_hilang_sistem ?? 0 }},
        rusakRinganSistem: {{ $stockOpname->jumlah_rusak_ringan_sistem ?? 0 }},
        rusakSedangSistem: {{ $stockOpname->jumlah_rusak_sedang_sistem ?? 0 }},
        rusakBeratSistem: {{ $stockOpname->jumlah_rusak_berat_sistem ?? 0 }},
        stokFisik: {{ old('stok_fisik', $stockOpname->stok_fisik) }},
        hilangDitemukan: {{ old('jumlah_hilang_ditemukan', $stockOpname->jumlah_hilang_ditemukan ?? 0) }},
        rusakRingan: {{ old('jumlah_rusak_ringan', $stockOpname->jumlah_rusak_ringan ?? 0) }},
        rusakSedang: {{ old('jumlah_rusak_sedang', $stockOpname->jumlah_rusak_sedang ?? 0) }},
        rusakBerat: {{ old('jumlah_rusak_berat', $stockOpname->jumlah_rusak_berat ?? 0) }},
        persenRingan: {{ $persenRusak['ringan'] }},
        persenSedang: {{ $persenRusak['sedang'] }},
        persenBerat: {{ $persenRusak['berat'] }},
        get rusakTotal() {
            return (parseInt(this.rusakRingan) || 0) + (parseInt(this.rusakSedang) || 0) + (parseInt(this.rusakBerat) || 0);
        },
        get selisih() {
            return (parseInt(this.stokFisik) || 0)
                + this.stokDipinjam
                + (parseInt(this.hilangDitemukan) || 0)
                + this.rusakTotal
                - this.stokMaster;
        },
        get selisihLabel() {
            if (this.selisih === 0) return 'Sesuai';
            return this.selisih > 0 ? 'Lebih ' + this.selisih : 'Kurang ' + Math.abs(this.selisih);
        }
    }" class="space-y-6 text-[11px] max-w-3xl">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.stock-opname.index') }}" class="text-gray-500 hover:text-gray-700"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h2 class="text-sm font-medium text-gray-800">Perbaiki Pengajuan Stok Opname</h2>
                <p class="text-[10px] text-gray-400">{{ $stockOpname->book->title ?? '-' }}</p>
            </div>
        </div>

        @if($stockOpname->rejection_reason)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <p class="font-medium text-[10px] uppercase mb-1">Alasan Penolakan</p>
                <p>{{ $stockOpname->rejection_reason }}</p>
            </div>
        @endif

        <form action="{{ route('admin.stock-opname.update', $stockOpname) }}" method="POST" enctype="multipart/form-data" class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Stok Baik Sistem</p>
                    <p class="text-lg font-medium" x-text="stokSistem"></p>
                </div>
                <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Dipinjam</p>
                    <p class="text-lg font-medium text-blue-600" x-text="stokDipinjam"></p>
                </div>
                <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Stok Master</p>
                    <p class="text-lg font-medium" x-text="stokMaster"></p>
                </div>
                <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Hilang Tercatat</p>
                    <p class="text-lg font-medium text-red-600" x-text="hilangSistem"></p>
                </div>
            </div>

            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Stok Baik di Rak <span class="text-red-500">*</span></label>
                <input type="number" name="stok_fisik" x-model="stokFisik" min="0" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px]">
                @error('stok_fisik') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="bg-red-50 border border-red-100 p-4 rounded-xl">
                <label class="block text-red-700 font-medium mb-1.5">Buku Hilang Ditemukan</label>
                <input type="number" name="jumlah_hilang_ditemukan" x-model="hilangDitemukan" min="0"
                    class="w-full px-3 py-2 border border-red-200 rounded-lg text-[11px] bg-white">
            </div>

            <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl space-y-3">
                <p class="text-orange-700 font-medium">Buku Rusak Ditemukan</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] text-orange-700 mb-1">Ringan ({{ $persenRusak['ringan'] }}%)</label>
                        <input type="number" name="jumlah_rusak_ringan" x-model="rusakRingan" min="0"
                            class="w-full px-3 py-2 border border-orange-200 rounded-lg text-[11px] bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] text-orange-700 mb-1">Sedang ({{ $persenRusak['sedang'] }}%)</label>
                        <input type="number" name="jumlah_rusak_sedang" x-model="rusakSedang" min="0"
                            class="w-full px-3 py-2 border border-orange-200 rounded-lg text-[11px] bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] text-orange-700 mb-1">Berat ({{ $persenRusak['berat'] }}%)</label>
                        <input type="number" name="jumlah_rusak_berat" x-model="rusakBerat" min="0"
                            class="w-full px-3 py-2 border border-orange-200 rounded-lg text-[11px] bg-white">
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl">
                <p class="text-[10px] text-blue-600 uppercase font-medium">Selisih</p>
                <p class="text-lg font-medium" :class="selisih === 0 ? 'text-green-600' : 'text-amber-600'" x-text="selisihLabel"></p>
            </div>

            @if($stockOpname->foto_path)
                <div>
                    <p class="text-gray-500 font-medium mb-1.5">Foto Bukti Saat Ini</p>
                    <img src="{{ asset('storage/' . $stockOpname->foto_path) }}" alt="Bukti opname" class="max-h-40 rounded-lg border border-gray-200">
                </div>
            @endif

            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Ganti Foto Bukti (opsional)</label>
                <input type="file" name="foto" accept="image/jpeg,image/png,image/jpg"
                    class="w-full text-[11px] file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600">
            </div>

            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Catatan</label>
                <textarea name="catatan" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px]">{{ old('catatan', $stockOpname->catatan) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
                    <i class="fas fa-paper-plane mr-1"></i> Kirim Ulang
                </button>
                <a href="{{ route('admin.stock-opname.index') }}" class="text-gray-500 px-4 py-2">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>
