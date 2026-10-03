<x-layouts.app title="Ajukan Penambahan Stok">
    <div x-data="{
        bookId: '{{ $selectedBookId }}',
        stokSebelum: 0,
        jumlahDitambah: {{ old('jumlah_ditambah', 1) }},
        books: @js($books),
        get stokSesudah() { return (parseInt(this.stokSebelum) || 0) + (parseInt(this.jumlahDitambah) || 0); },
        get hasPending() {
            const book = this.books.find(b => b.id == this.bookId);
            return book ? book.pending : false;
        },
        selectBook() {
            const book = this.books.find(b => b.id == this.bookId);
            this.stokSebelum = book ? book.stock : 0;
        },
        init() { if (this.bookId) this.selectBook(); }
    }" class="space-y-6 text-[11px] max-w-3xl">
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">{{ session('error') }}</div>
        @endif

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.stock-addition.index') }}" class="text-gray-500"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h2 class="text-sm font-medium">Form Penambahan Stok</h2>
                <p class="text-[10px] text-gray-400">Untuk judul buku yang sudah ada di Data Master. Stok berubah setelah Kepala menyetujui.</p>
            </div>
        </div>

        <form action="{{ route('admin.stock-addition.store') }}" method="POST" enctype="multipart/form-data" class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Pilih Buku <span class="text-red-500">*</span></label>
                <select name="book_id" x-model="bookId" @change="selectBook()" required class="w-full px-3 py-2 border border-gray-200 rounded-lg">
                    <option value="">-- Pilih Buku --</option>
                    @foreach($books as $book)
                        <option value="{{ $book['id'] }}" @selected($selectedBookId == $book['id'])>{{ $book['book_code'] }} — {{ $book['title'] }}</option>
                    @endforeach
                </select>
                <p x-show="hasPending" x-cloak class="text-amber-600 text-[10px] mt-1">Buku ini masih menunggu validasi penambahan stok.</p>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-gray-50 p-4 rounded-xl">
                <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Stok Saat Ini</p>
                    <p class="text-lg font-medium" x-text="stokSebelum"></p>
                </div>
                <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Ditambah</p>
                    <p class="text-lg font-medium text-emerald-600" x-text="'+' + (parseInt(jumlahDitambah) || 0)"></p>
                </div>
                <div class="text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Stok Sesudah</p>
                    <p class="text-lg font-medium text-blue-600" x-text="stokSesudah"></p>
                </div>
            </div>

            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Jumlah Stok Ditambah <span class="text-red-500">*</span></label>
                <input type="number" name="jumlah_ditambah" x-model="jumlahDitambah" min="1" required class="w-full px-3 py-2 border border-gray-200 rounded-lg">
                @error('jumlah_ditambah') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Foto Bukti (opsional)</label>
                <input type="file" name="foto" accept="image/jpeg,image/png,image/jpg" class="w-full text-[11px]">
            </div>
            <div>
                <label class="block text-gray-500 font-medium mb-1.5">Catatan (opsional)</label>
                <textarea name="catatan" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg">{{ old('catatan') }}</textarea>
            </div>

            <button type="submit" :disabled="hasPending || !bookId" class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg">
                Ajukan Validasi
            </button>
        </form>
    </div>
</x-layouts.app>
