<x-layouts.app title="Tambah Buku Baru">
    <div class="max-w-5xl mx-auto py-4 px-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header Card -->
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center">
                <div>
                    <h1 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book-medical text-blue-600"></i> Form Entri Buku Baru
                    </h1>
                </div>
                <span class="text-[10px] text-gray-400 font-medium italic">* Wajib diisi</span>
            </div>

            @if ($errors->any())
            <div class="m-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center gap-2 text-red-600 font-bold mb-2 text-[12px]">
                    <i class="fas fa-exclamation-circle"></i> Terdapat Kesalahan pada Input Anda:
                </div>
                <ul class="list-disc list-inside text-[11px] text-red-500 ml-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Kiri: Data Utama -->
                    <div class="lg:col-span-8 space-y-6">
                        <!-- Grup 1: Identitas -->
                        <div class="space-y-4">
                            <h2 class="text-[10px] font-bold text-blue-600 uppercase tracking-widest border-b border-blue-50 pb-1">01. Identitas Buku</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Kode Buku <span class="text-red-500">*</span></label>
                                    <input type="text" name="book_code" value="{{ old('book_code') }}" 
                                           class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all {{ $errors->has('book_code') ? 'border-red-400' : '' }}" 
                                           placeholder="BK-001" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">ISBN</label>
                                    <input type="text" name="isbn" value="{{ old('isbn') }}" 
                                           class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                                           placeholder="978-602-...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Judul Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title') }}" 
                                       class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none {{ $errors->has('title') ? 'border-red-400' : '' }}" 
                                       placeholder="Contoh: Pemrograman Laravel" required>
                            </div>
                        </div>

                        <!-- Grup 2: Penulis & Lokasi -->
                        <div class="space-y-4">
                            <h2 class="text-[10px] font-bold text-blue-600 uppercase tracking-widest border-b border-blue-50 pb-1">02. Klasifikasi & Lokasi</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Pengarang <span class="text-red-500">*</span></label>
                                    <input type="text" name="author" value="{{ old('author') }}" 
                                           class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                                           placeholder="Nama Penulis" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Penerbit</label>
                                    <input type="text" name="publisher" value="{{ old('publisher') }}" 
                                           class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                                           placeholder="PT. Gramedia">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Kategori <span class="text-red-500">*</span></label>
                                    <select name="category_id" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none bg-white" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Lokasi Rak <span class="text-red-500">*</span></label>
                                    <select name="rak_id" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none bg-white" required>
                                        <option value="">Pilih Lokasi</option>
                                        @foreach($raks as $rak)
                                            <option value="{{ $rak->id }}">{{ $rak->nama_rak }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Atribut Lain & Gambar -->
                    <div class="lg:col-span-4 space-y-6">
                        <div class="p-4 bg-gray-50/50 rounded-xl border border-gray-100 space-y-5">
                            <h2 class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">03. Detail Tambahan</h2>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Tahun Terbit</label>
                                    <input type="number" name="publication_year" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none" placeholder="2024">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Stok Awal <span class="text-red-500">*</span></label>
                                    <input type="number" name="stock" value="1" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none" required>
                                    <p class="text-[9px] text-gray-400 mt-1 leading-relaxed">Hanya untuk judul baru. Buku yang sudah ada ditambah stoknya lewat
                                        <a href="{{ route('admin.stock-addition.create') }}" class="text-blue-600 hover:underline">Penambahan Stok</a>.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Deskripsi / Sinopsis</label>
                                <textarea name="description" rows="4" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none resize-none" placeholder="Masukkan sinopsis buku (opsional)">{{ old('description') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Harga Buku (Rp)</label>
                                <input type="number" name="price" value="{{ old('price') }}" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none" placeholder="Contoh: 50000">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Gambar Sampul</label>
                                <div class="mt-1 flex flex-col items-center">
                                    <div id="previewContainer" class="w-full aspect-3/4 mb-3 bg-white border border-dashed border-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                                        <img id="imagePreview" src="#" class="hidden w-full h-full object-cover">
                                        <div id="placeholderText" class="text-center p-4">
                                            <i class="fas fa-image text-gray-200 text-3xl mb-2"></i>
                                            <p class="text-[9px] text-gray-400">Belum ada file dipilih</p>
                                        </div>
                                    </div>
                                    <label class="w-full text-center px-4 py-2 bg-white border border-blue-200 text-blue-600 text-[10px] font-bold rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
                                        <i class="fas fa-upload mr-1"></i> Pilih File
                                        <input type="file" name="cover_image" class="hidden" onchange="previewImage(event)">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer: Actions -->
                <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-50 flex justify-end items-center gap-3">
                    <a href="{{ route('admin.books.index') }}" class="text-[11px] font-bold text-gray-400 hover:text-gray-600 px-4 py-2 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-8 py-2 rounded-lg shadow-lg shadow-blue-100 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Koleksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function previewImage(event) {
                const file = event.target.files[0];
                const preview = document.getElementById('imagePreview');
                const placeholder = document.getElementById('placeholderText');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            }
        </script>
    @endpush
</x-layouts.app>