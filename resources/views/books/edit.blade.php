<x-layouts.app title="Edit Buku - Manajemen Perpustakaan">
    <div class="max-w-5xl mx-auto py-4 px-4">
        <!-- Breadcrumb / Navigation -->
        <div class="flex items-center gap-2 text-[10px] uppercase tracking-widest text-gray-400 mb-4 font-bold">
            <a href="{{ route('admin.books.index') }}" class="hover:text-blue-600 transition-colors">Daftar Buku</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-gray-600">Perbarui Informasi Buku</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header Card -->
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center text-gray-800">
                <div>
                    <h1 class="text-sm font-bold flex items-center gap-2">
                        <i class="fas fa-edit text-blue-600"></i> Edit Buku: {{ $book->title }}
                    </h1>
                </div>
                <span class="text-[10px] text-gray-400 font-medium italic">ID Buku: #{{ $book->id }}</span>
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

            <form action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- SISI KIRI: DATA UTAMA (8 KOLOM) -->
                    <div class="lg:col-span-8 space-y-6">
                        
                        <!-- Grup 1: Identitas -->
                        <div class="space-y-4">
                            <h2 class="text-[10px] font-bold text-blue-600 uppercase tracking-widest border-b border-blue-50 pb-1">01. Identitas Buku</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Kode Buku</label>
                                    <input type="text" name="book_code" value="{{ $book->book_code }}" readonly
                                           class="w-full px-3 py-1.5 text-[12px] bg-gray-50 border border-gray-200 rounded-lg text-gray-400 cursor-not-allowed outline-none shadow-sm">
                                    <p class="text-[9px] text-gray-400 mt-1 italic">*Kode buku bersifat permanen.</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">ISBN</label>
                                    <input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" 
                                           class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none" 
                                           placeholder="978-602-...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Judul Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title', $book->title) }}" 
                                       class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none {{ $errors->has('title') ? 'border-red-400' : '' }}" 
                                       required>
                            </div>
                        </div>

                        <!-- Grup 2: Penulis & Lokasi -->
                        <div class="space-y-4">
                            <h2 class="text-[10px] font-bold text-green-600 uppercase tracking-widest border-b border-green-50 pb-1">02. Klasifikasi & Lokasi</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Pengarang <span class="text-red-500">*</span></label>
                                    <input type="text" name="author" value="{{ old('author', $book->author) }}" 
                                           class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none" 
                                           required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Penerbit</label>
                                    <input type="text" name="publisher" value="{{ old('publisher', $book->publisher) }}" 
                                           class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Kategori <span class="text-red-500">*</span></label>
                                    <select name="category_id" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none bg-white" required>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ $book->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Lokasi Rak <span class="text-red-500">*</span></label>
                                    <select name="rak_id" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none bg-white" required>
                                        @foreach($raks as $rak)
                                            <option value="{{ $rak->id }}" {{ $book->rak_id == $rak->id ? 'selected' : '' }}>{{ $rak->nama_rak }} ({{ $rak->lokasi }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SISI KANAN: ATRIBUT & SAMPUL (4 KOLOM) -->
                    <div class="lg:col-span-4 space-y-6">
                        <div class="p-4 bg-gray-50/50 rounded-xl border border-gray-100 space-y-5 text-gray-800">
                            <h2 class="text-[10px] font-bold text-purple-600 uppercase tracking-widest">03. Detail & Media</h2>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Tahun Terbit</label>
                                <input type="number" name="publication_year" value="{{ old('publication_year', $book->publication_year) }}" 
                                       class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Stok Saat Ini</label>
                                <input type="number" value="{{ $book->stock }}" readonly
                                       class="w-full px-3 py-1.5 text-[12px] bg-gray-50 border border-gray-200 rounded-lg text-gray-400 cursor-not-allowed outline-none shadow-sm">
                                <p class="text-[9px] text-gray-500 mt-1.5 leading-relaxed">
                                    Stok tidak diubah lewat edit data buku. Untuk menambah stok judul yang sudah ada, gunakan
                                    <a href="{{ route('admin.stock-addition.create') }}" class="text-blue-600 hover:underline font-medium">Penambahan Stok</a>.
                                    Untuk cek fisik, rusak, dan hilang, gunakan
                                    <a href="{{ route('admin.stock-opname.create') }}" class="text-blue-600 hover:underline font-medium">Stok Opname</a>.
                                </p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Deskripsi / Sinopsis</label>
                                <textarea name="description" rows="4" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none resize-none" placeholder="Masukkan sinopsis buku (opsional)">{{ old('description', $book->description) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Harga Buku (Rp)</label>
                                <input type="number" name="price" value="{{ old('price', $book->price) }}" class="w-full px-3 py-1.5 text-[12px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none" placeholder="Contoh: 50000">
                            </div>

                            <!-- Image Management -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-2 uppercase">Manajemen Sampul</label>
                                <div class="space-y-4">
                                    <!-- Current Image -->
                                    <div class="text-center p-2 bg-white rounded-lg border border-gray-100 shadow-sm">
                                        <p class="text-[9px] text-gray-400 uppercase font-bold mb-2">Sampul Saat Ini</p>
                                        @if($book->cover_image)
                                            <img src="{{ asset('covers/' . $book->cover_image) }}" class="h-28 mx-auto rounded shadow-sm border border-gray-200">
                                        @else
                                            <div class="h-28 w-20 mx-auto bg-gray-100 rounded flex items-center justify-center text-gray-300">
                                                <i class="fas fa-image text-2xl"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- New Preview -->
                                    <div id="newPreviewArea" class="hidden text-center p-2 bg-blue-50 rounded-lg border border-blue-100 border-dashed">
                                        <p class="text-[9px] text-blue-500 uppercase font-bold mb-2">Pratinjau Baru</p>
                                        <img id="imagePreview" src="#" class="h-28 mx-auto rounded shadow-sm">
                                    </div>

                                    <label class="w-full text-center px-4 py-2 bg-white border border-blue-200 text-blue-600 text-[10px] font-bold rounded-lg cursor-pointer hover:bg-blue-50 transition-colors block">
                                        <i class="fas fa-camera mr-1"></i> Ganti Sampul
                                        <input type="file" name="cover_image" class="hidden" onchange="previewImage(event)">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Card: Actions -->
                <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-50 flex justify-end items-center gap-3">
                    <a href="{{ route('admin.books.index') }}" class="text-[11px] font-bold text-gray-400 hover:text-gray-600 px-4 py-2 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-8 py-2 rounded-lg shadow-lg shadow-blue-100 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
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
                const area = document.getElementById('newPreviewArea');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        area.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            }
        </script>
    @endpush
</x-layouts.app>