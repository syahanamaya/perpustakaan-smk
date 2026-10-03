<x-layouts.app title="Ajukan Stok Opname">
    <div x-data="{
        bookId: '{{ $selectedBookId ?? old('book_id') }}',
        stokSistem: 0,
        stokDipinjam: 0,
        stokMaster: 0,
        hilangSistem: 0,
        rusakRinganSistem: 0,
        rusakSedangSistem: 0,
        rusakBeratSistem: 0,
        stokFisik: {{ old('stok_fisik', 0) }},
        hilangDitemukan: {{ old('jumlah_hilang_ditemukan', 0) }},
        rusakRingan: {{ old('jumlah_rusak_ringan', 0) }},
        rusakSedang: {{ old('jumlah_rusak_sedang', 0) }},
        rusakBerat: {{ old('jumlah_rusak_berat', 0) }},
        hasPending: false,
        books: @js($books),
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
        },
        selectBook() {
            const book = this.books.find(b => b.id == this.bookId);
            if (!book) {
                this.stokSistem = 0;
                this.stokDipinjam = 0;
                this.stokMaster = 0;
                this.hilangSistem = 0;
                this.rusakRinganSistem = 0;
                this.rusakSedangSistem = 0;
                this.rusakBeratSistem = 0;
                this.hasPending = false;
                this.stokFisik = 0;
                return;
            }
            this.stokSistem = book.stok_sistem;
            this.stokDipinjam = book.stok_dipinjam;
            this.stokMaster = book.stok_master;
            this.hilangSistem = book.jumlah_hilang_sistem;
            this.rusakRinganSistem = book.jumlah_rusak_ringan_sistem;
            this.rusakSedangSistem = book.jumlah_rusak_sedang_sistem;
            this.rusakBeratSistem = book.jumlah_rusak_berat_sistem;
            this.hasPending = book.has_pending;
            this.stokFisik = book.stok_sistem;
        },
        init() {
            if (this.bookId) this.selectBook();
        }
    }" class="space-y-6 text-[11px] max-w-3xl">

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">{{ session('error') }}</div>
        @endif

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.stock-opname.index') }}" class="text-gray-500 hover:text-gray-700"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h2 class="text-sm font-medium text-gray-800">Form Pengajuan Stok Opname</h2>
                <p class="text-[10px] text-gray-400">Cek stok fisik, kondisi rusak, dan buku hilang saat penghitungan di rak.</p>
            </div>
        </div>

        <form action="{{ route('admin.stock-opname.store') }}" method="POST" enctype="multipart/form-data" class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Pilih Buku <span class="text-red-500">*</span></label>
                <select name="book_id" x-model="bookId" @change="selectBook()" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px] focus:ring-1 focus:ring-blue-500">
                    <option value="">-- Pilih Buku --</option>
                    @foreach($books as $book)
                        <option value="{{ $book['id'] }}" @selected(($selectedBookId ?? old('book_id')) == $book['id'])>
                            {{ $book['book_code'] }} — {{ $book['title'] }}
                        </option>
                    @endforeach
                </select>
                @error('book_id') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                <p x-show="hasPending" x-cloak class="text-amber-600 text-[10px] mt-1">
                    <i class="fas fa-exclamation-triangle"></i> Buku ini masih memiliki pengajuan yang menunggu validasi.
                </p>
            </div>

            <div class="space-y-3">
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">Data Sistem Saat Ini</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="text-center">
                        <p class="text-[9px] text-gray-400 uppercase">Stok Baik (rak)</p>
                        <p class="text-lg font-medium text-gray-800" x-text="stokSistem"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[9px] text-gray-400 uppercase">Sedang Dipinjam</p>
                        <p class="text-lg font-medium text-blue-600" x-text="stokDipinjam"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[9px] text-gray-400 uppercase">Stok Master</p>
                        <p class="text-lg font-medium text-gray-800" x-text="stokMaster"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[9px] text-gray-400 uppercase">Sudah Tercatat Hilang</p>
                        <p class="text-lg font-medium text-red-600" x-text="hilangSistem"></p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 bg-orange-50 p-3 rounded-xl border border-orange-100">
                    <div class="text-center">
                        <p class="text-[9px] text-orange-600 uppercase">Rusak Ringan (<span x-text="persenRingan"></span>%)</p>
                        <p class="text-base font-medium text-orange-700" x-text="rusakRinganSistem"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[9px] text-orange-600 uppercase">Rusak Sedang (<span x-text="persenSedang"></span>%)</p>
                        <p class="text-base font-medium text-orange-700" x-text="rusakSedangSistem"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[9px] text-orange-600 uppercase">Rusak Berat (<span x-text="persenBerat"></span>%)</p>
                        <p class="text-base font-medium text-orange-700" x-text="rusakBeratSistem"></p>
                    </div>
                </div>
            </div>

            <div class="space-y-3 border-t border-gray-100 pt-4">
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">Hasil Pengecekan Fisik</p>

                <div>
                    <label class="block text-gray-500 font-medium mb-1.5">Stok Baik di Rak <span class="text-red-500">*</span></label>
                    <input type="number" name="stok_fisik" x-model="stokFisik" min="0" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px] focus:ring-1 focus:ring-blue-500">
                    <p class="text-[10px] text-gray-400 mt-1">Jumlah eksemplar kondisi baik yang ditemukan di rak.</p>
                    @error('stok_fisik') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-red-50 border border-red-100 p-4 rounded-xl space-y-2">
                    <label class="block text-red-700 font-medium">Buku Hilang Ditemukan Saat Opname</label>
                    <input type="number" name="jumlah_hilang_ditemukan" x-model="hilangDitemukan" min="0"
                        class="w-full px-3 py-2 border border-red-200 rounded-lg text-[11px] focus:ring-1 focus:ring-red-400 bg-white">
                    <p class="text-[10px] text-red-600">Isi jumlah buku yang tidak ditemukan saat penghitungan fisik. Denda hilang = 100% harga buku (sama seperti pengembalian).</p>
                    @error('jumlah_hilang_ditemukan') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl space-y-3">
                    <p class="text-orange-700 font-medium">Buku Rusak Ditemukan Saat Opname</p>
                    <p class="text-[10px] text-orange-600">Sesuaikan tingkat kerusakan dengan kriteria denda perpustakaan (sama seperti form pengembalian).</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] text-orange-700 mb-1">Rusak Ringan ({{ $persenRusak['ringan'] }}%)</label>
                            <input type="number" name="jumlah_rusak_ringan" x-model="rusakRingan" min="0"
                                class="w-full px-3 py-2 border border-orange-200 rounded-lg text-[11px] bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] text-orange-700 mb-1">Rusak Sedang ({{ $persenRusak['sedang'] }}%)</label>
                            <input type="number" name="jumlah_rusak_sedang" x-model="rusakSedang" min="0"
                                class="w-full px-3 py-2 border border-orange-200 rounded-lg text-[11px] bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] text-orange-700 mb-1">Rusak Berat ({{ $persenRusak['berat'] }}%)</label>
                            <input type="number" name="jumlah_rusak_berat" x-model="rusakBerat" min="0"
                                class="w-full px-3 py-2 border border-orange-200 rounded-lg text-[11px] bg-white">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl">
                <p class="text-[10px] text-blue-600 uppercase font-medium mb-1">Hasil Rekonsiliasi</p>
                <p class="text-sm text-gray-700">Selisih = (Stok Baik + Dipinjam + Hilang + Rusak) − Stok Master</p>
                <p class="text-lg font-medium mt-1" :class="selisih === 0 ? 'text-green-600' : 'text-amber-600'" x-text="selisihLabel"></p>
            </div>

            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Foto Bukti (opsional)</label>
                <input type="file" name="foto" accept="image/jpeg,image/png,image/jpg"
                    class="w-full text-[11px] file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600">
                <p class="text-[10px] text-gray-400 mt-1">Hanya sebagai bukti pendukung, tidak masuk laporan PDF/Excel.</p>
                @error('foto') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Catatan (opsional)</label>
                <textarea name="catatan" rows="3" placeholder="Keterangan tambahan tentang kondisi buku..."
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px] focus:ring-1 focus:ring-blue-500">{{ old('catatan') }}</textarea>
                @error('catatan') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" :disabled="hasPending || !bookId"
                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-5 py-2 rounded-lg font-medium">
                    <i class="fas fa-paper-plane mr-1"></i> Ajukan Validasi
                </button>
                <a href="{{ route('admin.stock-opname.index') }}" class="text-gray-500 hover:text-gray-700 px-4 py-2">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>
