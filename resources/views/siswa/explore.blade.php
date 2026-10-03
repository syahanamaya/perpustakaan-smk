<x-layouts.app title="Jelajah Buku">
    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        [x-cloak] { display: none !important; }
    </style>
    @endsection

    <div x-data="{ 
            modalOpen: false, 
            activeBook: {
                id: '',          
                title: '',
                author: '',
                category: '',
                cover: '',
                stock: 0,
                description: '',
                publisher: '',
                year: '',
                isFavorited: false
            },
            openModal(bookData) {
                this.activeBook = bookData;
                this.modalOpen = true;
                document.body.style.overflow = 'hidden'; 
            },
            closeModal() {
                this.modalOpen = false;
                document.body.style.overflow = ''; 
            },
            toggleFavorite() {
                fetch('{{ route('student.favorite.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ book_id: this.activeBook.id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'added') {
                        this.activeBook.isFavorited = true;
                    } else if (data.status === 'removed') {
                        this.activeBook.isFavorited = false;
                    }
                }).catch(error => console.error('Error:', error));
            }
         }" 
         class="max-w-7xl mx-auto px-6 py-6 space-y-6 min-h-screen pb-16">

        {{-- BARIS ATAS: Judul Halaman & Bar Pencarian Utama --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-20">
            <div class="space-y-0.5 shrink-0">
                <h2 class="text-xl font-medium text-blue-800">Cari Buku</h2>
                <p class="text-[11px] text-gray-400 mt-0.5">Temukan koleksi buku yang kamu butuhkan.</p>
            </div>

            <form action="{{ route('student.explore') }}" method="GET" class="w-full md:w-lg relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="w-full pl-10 pr-12 py-2.5 bg-white border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-[11px] text-gray-700 placeholder-gray-400 shadow-sm transition-all" 
                       placeholder="Cari judul, penulis, kategori, ISBN, atau penerbit...">
                <button type="submit" class="absolute right-1 top-1 bottom-1 bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-full flex items-center justify-center transition-all">
                    <i class="fas fa-search text-xs"></i>
                </button>
            </form>
        </div>

        {{-- LAYOUT UTAMA: SIDEBAR FILTER (KIRI) & KONTEN GRID BUKU (KANAN) --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            {{-- BLOCK SIDEBAR: FILTER PENCARIAN --}}
            <div class="lg:col-span-1 bg-white p-5 rounded-2xl border border-gray-150 shadow-sm space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                    <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wide">Filter Pencarian</h3>
                    <a href="{{ route('student.explore') }}" class="text-[10px] text-blue-600 hover:underline flex items-center gap-1">
                        <i class="fas fa-undo text-[9px]"></i> Reset
                    </a>
                </div>

                <form action="{{ route('student.explore') }}" method="GET" class="space-y-4">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase mb-1.5">Kategori</label>
                        <select name="category" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-[11px] text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase mb-1.5">Penulis</label>
                        <select name="author" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-[11px] text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Penulis</option>
                            </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase mb-1.5">Penerbit</label>
                        <select name="publisher" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-[11px] text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Penerbit</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase mb-1.5">Tahun Terbit</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="year_from" value="{{ request('year_from') }}" placeholder="Dari tahun" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-[11px] text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-400 text-xs">-</span>
                            <input type="number" name="year_to" value="{{ request('year_to') }}" placeholder="Sampai tahun" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-[11px] text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-medium text-gray-500 uppercase mb-1">Ketersediaan</label>
                        <label class="flex items-center gap-2 text-[11px] text-gray-600 cursor-pointer">
                            <input type="radio" name="availability" value="" {{ !request('availability') ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 border-gray-300">
                            Semua
                        </label>
                        <label class="flex items-center gap-2 text-[11px] text-gray-600 cursor-pointer">
                            <input type="radio" name="availability" value="available" {{ request('availability') == 'available' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 border-gray-300">
                            Tersedia
                        </label>
                        <label class="flex items-center gap-2 text-[11px] text-gray-600 cursor-pointer">
                            <input type="radio" name="availability" value="empty" {{ request('availability') == 'empty' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 border-gray-300">
                            Tidak Tersedia
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-medium shadow-sm transition-all mt-2">
                        Terapkan Filter
                    </button>
                </form>
            </div>

            {{-- BLOCK KANAN: PANEL GRID DAFTAR BUKU --}}
            <div class="lg:col-span-3 space-y-4">
                
                @if(isset($popularBooks) && $popularBooks->count() > 0 && !request()->has('search') && !request()->has('category'))
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-medium text-gray-800 flex items-center gap-2">
                            <i class="fas fa-fire text-orange-500"></i> Buku Sering Dipinjam
                        </h3>
                        <span class="text-[10px] text-gray-400">Rekomendasi berdasarkan jumlah peminjaman</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                        @foreach($popularBooks as $pop)
                        <div class="rounded-xl border border-gray-100 p-2 hover:border-blue-200 transition">
                            <div class="aspect-3/4 rounded-lg overflow-hidden bg-gray-50 mb-2 relative">
                                @if($pop->cover_image)
                                    <img src="{{ asset('covers/' . $pop->cover_image) }}" class="w-full h-full object-cover" alt="{{ $pop->title }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="fas fa-book"></i></div>
                                @endif
                                <span class="absolute top-1 left-1 bg-orange-500 text-white text-[8px] px-1.5 py-0.5 rounded">{{ (int) $pop->borrow_count }}x</span>
                            </div>
                            <p class="text-[10px] font-medium text-gray-800 line-clamp-2 leading-tight">{{ $pop->title }}</p>
                            <p class="text-[9px] text-gray-400 truncate">{{ $pop->author }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Meta Info Atas: Informasi Total Buku & Dropdown Sort --}}
                <div class="flex justify-between items-center bg-transparent px-1">
                    <p class="text-[11px] text-gray-500 font-medium">
                        Menampilkan <span class="text-gray-800 font-medium">{{ $books->firstItem() ?? 0 }} - {{ $books->lastItem() ?? 0 }}</span> dari <span class="text-gray-800 font-medium">{{ $books->total() }}</span> buku
                    </p>
                    
                    <div class="flex items-center gap-2">
                        <label class="text-[11px] text-gray-400">Urutkan:</label>
                        <select name="sort" class="bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-[11px] text-gray-700 focus:outline-none font-medium shadow-sm cursor-pointer">
                            <option value="latest">Terbaru</option>
                            <option value="oldest">Terlama</option>
                            <option value="alphabetical">A - Z</option>
                        </select>
                    </div>
                </div>

                {{-- Grid Tampilan Buku (Sesuai Gambar Grid 6 Kolom) --}}
                @if($books->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($books as $book)
                            <div class="bg-white rounded-2xl p-3 border border-gray-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-all h-full">
                                <div>
                                    <div class="aspect-3/4 w-full rounded-xl overflow-hidden bg-gray-50 mb-3 border border-gray-100 relative shadow-inner">
                                        @if($book->cover_image)
                                            <img src="{{ asset('covers/' . $book->cover_image) }}" class="w-full h-full object-cover" alt="{{ $book->title }}">
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                                                <i class="fas fa-book text-2xl mb-1"></i>
                                                <span class="text-[9px]">No Cover</span>
                                            </div>
                                        @endif
                                    </div>

                                    <h4 class="text-xs font-medium text-gray-800 line-clamp-1 leading-snug" title="{{ $book->title }}">{{ $book->title }}</h4>
                                    <p class="text-[10px] text-gray-400 mt-0.5 truncate">{{ $book->author }}</p>
                                    <p class="text-[9px] font-medium text-blue-600 bg-blue-50/50 rounded px-1.5 py-0.5 mt-1.5 inline-block">{{ $book->category->name ?? 'Umum' }}</p>
                                    
                                    <div class="flex items-center gap-1.5 mt-2 text-[10px]">
                                        @if($book->stock > 4)
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            <span class="text-green-600 font-medium">Tersedia ({{ $book->stock }})</span>
                                        @elseif($book->stock > 0)
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span class="text-amber-600 font-medium">Stok Terbatas ({{ $book->stock }})</span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            <span class="text-red-500 font-medium">Tidak Tersedia</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Trigger Pembuatan Data Objek Alpine.js Detail Modal --}}
                                @php
                                    $isFavorited = \App\Models\Favorite::where('student_id', auth()->guard('student')->id())->where('book_id', $book->id)->exists();
                                    $bookData = [
                                        'id' => $book->id,
                                        'title' => $book->title,
                                        'author' => $book->author,
                                        'category' => $book->category->name ?? 'Umum',
                                        'cover' => $book->cover_image ? asset('covers/' . $book->cover_image) : null,
                                        'stock' => $book->stock,
                                        'description' => $book->description ?? 'Belum ada deskripsi untuk buku ini.',
                                        'publisher' => $book->publisher ?? '-',
                                        'year' => $book->publish_year ?? '-',
                                        'isFavorited' => $isFavorited
                                    ];
                                @endphp

                                <button @click='openModal(@json($bookData))' 
                                        class="w-full mt-3 py-1.5 border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl text-[10px] font-medium transition-all shadow-sm text-center">
                                    Lihat Detail
                                </button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Wrapper Navigasi Halaman Pagination Tengah --}}
                    <div class="mt-8 flex justify-center">
                        {{ $books->withQueryString()->links() }}
                    </div>

                @else
                    {{-- Blok Tampilan Jika Buku Tidak Ditemukan --}}
                    <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm text-center px-4">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-search-minus text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-sm font-medium text-gray-800 mb-1">Buku tidak ditemukan</h3>
                        <p class="text-[10px] text-gray-400 max-w-xs mx-auto">Coba sesuaikan kata kunci atau reset filter pencarian di panel kiri.</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- MODAL CONTAINER DETAIL BUKU --}}
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-100 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="modalOpen" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl border border-gray-100">
                    
                    <button @click="closeModal" class="absolute top-4 right-4 w-9 h-9 bg-gray-100 hover:bg-red-100 hover:text-red-600 rounded-full flex items-center justify-center text-gray-500 transition-colors z-10">
                        <i class="fas fa-times text-xs"></i>
                    </button>

                    <div class="bg-white p-6 sm:p-8">
                        <div class="sm:flex sm:items-start gap-6">
                            <div class="text-center sm:text-left shrink-0">
                                <div class="w-40 h-56 bg-gray-100 rounded-xl overflow-hidden shadow-sm border border-gray-200 relative mx-auto">
                                    <template x-if="activeBook.cover">
                                        <img :src="activeBook.cover" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!activeBook.cover">
                                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 bg-gray-50">
                                            <i class="fas fa-book text-4xl mb-1"></i>
                                            <span class="text-[10px]">No Cover</span>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-3 flex items-center justify-center sm:justify-start gap-1.5 text-[11px] font-medium py-1.5 px-3 rounded-lg border"
                                     :class="activeBook.stock > 0 ? 'text-green-600 bg-green-50 border-green-100' : 'text-red-600 bg-red-50 border-red-100'">
                                    <i :class="activeBook.stock > 0 ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i> 
                                    Stok: <span x-text="activeBook.stock"></span> Eks
                                </div>
                            </div>

                            <div class="mt-4 sm:mt-0 flex-1 relative">
                                <button @click="toggleFavorite()" 
                                        class="absolute -top-4 right-8 w-9 h-9 bg-white rounded-full flex items-center justify-center shadow border border-gray-100 z-10"
                                        title="Simpan Favorit">
                                    <i class="text-sm transition-colors" :class="activeBook.isFavorited ? 'fas fa-heart text-red-500' : 'far fa-heart text-gray-400'"></i>
                                </button>

                                <div class="text-[9px] font-medium px-2 py-0.5 bg-gray-100 text-gray-500 rounded inline-block mb-2 uppercase tracking-wide">
                                    <span x-text="activeBook.category"></span>
                                </div>
                                
                                <h3 class="text-xl font-medium text-gray-900 mb-1" x-text="activeBook.title"></h3>
                                <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                                    <i class="fas fa-user text-gray-400 text-[10px]"></i> Penulis: <span class="text-gray-700 font-medium" x-text="activeBook.author"></span>
                                </p>

                                <div class="grid grid-cols-2 gap-3 mb-4 p-3 bg-gray-50 rounded-xl border border-gray-100 text-[11px]">
                                    <div>
                                        <p class="text-gray-400 text-[10px] mb-0.5">Penerbit</p>
                                        <p class="font-medium text-gray-700" x-text="activeBook.publisher"></p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 text-[10px] mb-0.5">Tahun Terbit</p>
                                        <p class="font-medium text-gray-700" x-text="activeBook.year"></p>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-medium text-gray-800 mb-1">Sinopsis / Deskripsi:</h4>
                                    <p class="text-[11px] text-gray-500 leading-relaxed text-justify max-h-32 overflow-y-auto pr-1" x-text="activeBook.description"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 sm:px-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-amber-600 bg-amber-50/70 px-3 py-2 rounded-xl border border-amber-100 w-full sm:w-auto">
                            <i class="fas fa-info-circle text-xs shrink-0"></i>
                            <p class="text-[10px] font-medium leading-tight">
                                Silahkan lakukan peminjaman fisik langsung melalui petugas di meja sirkulasi perpustakaan.
                            </p>
                        </div>
                        <button type="button" @click="closeModal" class="w-full sm:w-auto px-6 py-2 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-700 hover:bg-gray-100 shadow-sm transition-all">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>