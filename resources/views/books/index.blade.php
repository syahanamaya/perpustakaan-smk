<x-layouts.app title="Data Buku">
    <div x-data="{ 
        selectedBook: null,
        openMenuId: null,
        borrowHistory: { loading: false, total: 0, borrower_count: 0, borrowers: [], recent: [] },
        async loadBorrowHistory(id) {
            this.borrowHistory = { loading: true, total: 0, borrower_count: 0, borrowers: [], recent: [] };
            try {
                const res = await fetch('/admin/books/' + id + '/borrow-history', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.borrowHistory = Object.assign({ loading: false }, data);
            } catch (e) {
                this.borrowHistory.loading = false;
            }
        }
    }" class="space-y-6 text-[11px] text-gray-600">
        
        @php $opname = $opnameStats ?? ['total_judul' => 0, 'opname_verified' => 0, 'opname_unverified' => 0, 'opname_masalah' => 0, 'persen_verified' => 0]; @endphp
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-lg">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Total Buku</p>
                    <h3 id="stat-total-buku" class="text-[16px] font-medium text-gray-800">{{ number_format($total_buku ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400 font-light">Semua buku</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex items-center justify-center text-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Buku Tersedia</p>
                    <h3 id="stat-buku-tersedia" class="text-[16px] font-medium text-gray-800">{{ number_format($buku_tersedia ?? 0, 0, ',', '.') }}</h3>
                    <p id="stat-persen-tersedia" class="text-[9px] text-gray-400 font-light">{{ $persen_tersedia ?? 0 }}% dari total buku</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center text-lg">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Buku Dipinjam</p>
                    <h3 id="stat-buku-dipinjam" class="text-[16px] font-medium text-gray-800">{{ number_format($buku_dipinjam ?? 0, 0, ',', '.') }}</h3>
                    <p id="stat-persen-dipinjam" class="text-[9px] text-gray-400 font-light">{{ $persen_dipinjam ?? 0 }}% dari total buku</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-lg">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Buku Rusak / Hilang</p>
                    <h3 id="stat-buku-rusak" class="text-[16px] font-medium text-gray-800">{{ number_format($buku_rusak ?? 0, 0, ',', '.') }}</h3>
                    <p id="stat-persen-rusak" class="text-[9px] text-gray-400 font-light">{{ $persen_rusak ?? 0 }}% dari total buku</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-slate-50 text-slate-500 flex items-center justify-center text-lg">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Total Judul (Opname)</p>
                    <h3 id="stat-opname-total" class="text-[16px] font-medium text-gray-800">{{ number_format($opname['total_judul'], 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400 font-light">Judul yang dicek stoknya</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Terverifikasi</p>
                    <h3 id="stat-opname-verified" class="text-[16px] font-medium text-gray-800">{{ number_format($opname['opname_verified'], 0, ',', '.') }}</h3>
                    <p id="stat-opname-persen" class="text-[9px] text-gray-400 font-light">{{ $opname['persen_verified'] }}% judul sudah dicek</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center text-lg">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Belum Dicek</p>
                    <h3 id="stat-opname-unverified" class="text-[16px] font-medium text-gray-800">{{ number_format($opname['opname_unverified'], 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400 font-light">Tanpa rusak / hilang</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center text-lg">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Rusak / Hilang (Opname)</p>
                    <h3 id="stat-opname-masalah" class="text-[16px] font-medium text-gray-800">{{ number_format($opname['opname_masalah'], 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400 font-light">Status bermasalah</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form action="{{ route('admin.books.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-50">
                    <label class="block text-gray-400 font-medium mb-1">Cari Buku</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik judul, pengarang, atau ISBN..." class="w-full pl-3 pr-10 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-300"></i>
                    </div>
                </div>
                <div class="w-44">
                    <label class="block text-gray-400 font-medium mb-1">Kategori</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="">Semua Kategori</option>
                        @if(isset($categories))
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="w-44">
                    <label class="block text-gray-400 font-medium mb-1">Rak</label>
                    <select name="rak" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="">Semua Rak</option>
                        @if(isset($raks))
                            @foreach($raks as $r)
                                <option value="{{ $r->id }}" {{ request('rak') == $r->id ? 'selected' : '' }}>{{ $r->nama_rak }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="w-44">
                    <label class="block text-gray-400 font-medium mb-1">Status Opname</label>
                    <select name="stock_status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="">Semua Status</option>
                        <option value="verified" {{ request('stock_status') == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                        <option value="damaged" {{ request('stock_status') == 'damaged' ? 'selected' : '' }}>Rusak</option>
                        <option value="missing" {{ request('stock_status') == 'missing' ? 'selected' : '' }}>Hilang</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fas fa-filter text-[10px]"></i> Terapkan Filter
                </button>
                <a href="{{ route('admin.books.index') }}" class="border border-gray-200 text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-50 transition flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Reset
                </a>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            <div class="lg:col-span-4 bg-white p-4 rounded-xl border border-dashed border-green-400 shadow-sm flex flex-col justify-center">
                <h3 class="text-[12px] font-medium text-gray-800">Import Data Buku</h3>
                <p class="text-[10px] text-gray-500 mt-1">Import banyak data buku sekaligus dari file Excel</p>
                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <form action="{{ route('admin.books.import') }}" method="POST" enctype="multipart/form-data" class="m-0 flex">
                        @csrf
                        <input type="file" name="file" id="import_book_file" class="hidden" onchange="this.form.submit()" accept=".xlsx,.xls,.csv">
                        <label for="import_book_file" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg font-medium flex items-center gap-1.5 transition text-[10px] cursor-pointer mb-0">
                            <i class="fas fa-upload"></i> Import Excel
                        </label>
                    </form>
                    <a href="{{ route('admin.books.template') }}" class="bg-white border border-blue-500 text-blue-500 hover:bg-blue-50 px-3 py-1.5 rounded-lg font-medium flex items-center gap-1.5 transition text-[10px]">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
            </div>

            <div class="lg:col-span-8 bg-[#f8fbff] p-4 rounded-xl border border-blue-100 shadow-sm relative overflow-hidden">
                <div class="flex items-center gap-2 text-blue-600 font-medium mb-3 text-[11px]">
                    <i class="fas fa-info-circle"></i> Panduan Import
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative z-10">
                    <div>
                        <ol class="list-decimal list-inside text-[10px] text-gray-600 space-y-1">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format yang tersedia</li>
                            <li>Upload file Excel (.xlsx, .xls, atau .csv)</li>
                            <li>Pastikan data tidak ada yang kosong pada kolom wajib</li>
                        </ol>
                    </div>
                    <div>
                        <ul class="text-[10px] text-gray-600 space-y-1">
                            <li class="flex items-start gap-1.5">
                                <i class="fas fa-check text-gray-500 mt-0.5"></i> 
                                <span>Kolom Wajib: Judul Buku, Pengarang, Kategori, Stok</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <i class="fas fa-check text-gray-500 mt-0.5"></i> 
                                <span>Maksimal ukuran file: 5MB</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <i class="fas fa-file-excel text-green-500 opacity-60 text-3xl absolute bottom-3 right-4"></i>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            
            <div :class="selectedBook ? 'col-span-12 lg:col-span-8' : 'col-span-12'" class="transition-all duration-300">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50 flex justify-between items-center text-gray-800 font-medium">
                        <h2 class="text-sm">Daftar Buku <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-[10px] text-gray-400 font-normal">{{ isset($books) ? $books->total() : '5' }} data</span></h2>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.stock-addition.index') }}" class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-4 py-1.5 rounded-lg font-medium hover:bg-emerald-100 flex items-center gap-2 shadow-sm transition">
                                <i class="fas fa-boxes"></i> Riwayat Penambahan Stok
                            </a>
                            <a href="{{ route('admin.books.create') }}" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg font-medium hover:bg-blue-700 flex items-center gap-2 shadow-sm transition">
                                <i class="fas fa-plus"></i> Tambah Buku
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-[9px] text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-3 font-light text-center">No.</th>
                                    <th class="px-4 py-3 font-light">ISBN</th>
                                    <th class="px-4 py-3 font-light">Judul Buku</th>
                                    <th class="px-4 py-3 font-light" :class="selectedBook ? 'hidden' : ''">Pengarang</th>
                                    <th class="px-4 py-3 font-light text-center" :class="selectedBook ? 'hidden' : ''">Kategori</th>
                                    <th class="px-4 py-3 font-light text-center">Rak</th>
                                    <th class="px-4 py-3 font-light text-center">Stok</th>
                                    <th class="px-4 py-3 font-light text-center">Keterangan</th>
                                    <th class="px-4 py-3 font-light text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @if(isset($books))
                                    @foreach($books as $index => $book)
                                    @php
                                        $soStatus = $book->derivedStockStatus();
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 transition-colors" data-book-row="{{ $book->id }}" data-stock-status="{{ $soStatus }}" :class="selectedBook && selectedBook.id === {{ $book->id }} ? 'bg-blue-50/50' : ''">
                                        <td class="px-4 py-3 text-gray-400 text-center">{{ $books->firstItem() + $index }}</td>
                                        <td class="px-4 py-3 text-gray-400">{{ $book->isbn ?? '-' }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-800">
                                            <div class="truncate" :class="selectedBook ? 'max-w-37.5' : ''">{{ $book->title }}</div>
                                        </td>
                                        <td class="px-4 py-3" :class="selectedBook ? 'hidden' : ''">{{ $book->author }}</td>
                                        <td class="px-4 py-3 text-center" :class="selectedBook ? 'hidden' : ''">
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-500 rounded text-[9px]">{{ $book->category->name ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-500">{{ $book->rak->nama_rak ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center font-medium" data-cell="stock">{{ $book->stock }}</td>
                                        <td class="px-4 py-3" data-cell="opname-badge">
                                            @php
                                                $hilang = (int) ($book->jumlah_hilang ?? 0);
                                                $rusakRingan = (int) ($book->jumlah_rusak_ringan ?? 0);
                                                $rusakSedang = (int) ($book->jumlah_rusak_sedang ?? 0);
                                                $rusakBerat = (int) ($book->jumlah_rusak_berat ?? 0);
                                                $dipinjamCount = (int) ($book->dipinjam_count ?? 0);
                                                $additionTotal = (int) (
                                                    $book->addition_disetujui_total
                                                    ?? $book->addition_disetujui_total_sum_jumlah_ditambah
                                                    ?? 0
                                                );
                                                $hasIssue = $hilang > 0 || $rusakRingan > 0 || $rusakSedang > 0 || $rusakBerat > 0 || $dipinjamCount > 0;
                                            @endphp

                                            <div class="text-[10px] leading-relaxed space-y-0.5">
                                                @if(!$hasIssue && $additionTotal <= 0)
                                                    <span class="text-emerald-600">Masih lengkap</span>
                                                @endif

                                                @if($dipinjamCount > 0)
                                                    <p class="text-blue-600">Dipinjam {{ $dipinjamCount }}</p>
                                                @endif
                                                @if($hilang > 0)
                                                    <p class="text-red-600">Hilang {{ $hilang }}</p>
                                                @endif
                                                @if($rusakRingan > 0)
                                                    <p class="text-orange-500">Rusak ringan {{ $rusakRingan }}</p>
                                                @endif
                                                @if($rusakSedang > 0)
                                                    <p class="text-orange-600">Rusak sedang {{ $rusakSedang }}</p>
                                                @endif
                                                @if($rusakBerat > 0)
                                                    <p class="text-orange-800">Rusak berat {{ $rusakBerat }}</p>
                                                @endif
                                                @if($additionTotal > 0)
                                                    <p class="text-emerald-600">Tambah stok +{{ $additionTotal }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button @click="selectedBook = {
                                                    id: {{ $book->id }},
                                                    title: '{{ addslashes($book->title) }}',
                                                    author: '{{ addslashes($book->author) }}',
                                                    isbn: '{{ $book->isbn }}',
                                                    category: '{{ $book->category->name ?? '-' }}',
                                                    rak: '{{ $book->rak->nama_rak ?? '-' }}',
                                                    stock: '{{ $book->stock }}',
                                                    dipinjam_count: {{ (int) ($book->dipinjam_count ?? 0) }},
                                                    hilang: {{ (int) ($book->jumlah_hilang ?? 0) }},
                                                    rusak_ringan: {{ (int) ($book->jumlah_rusak_ringan ?? 0) }},
                                                    rusak_sedang: {{ (int) ($book->jumlah_rusak_sedang ?? 0) }},
                                                    rusak_berat: {{ (int) ($book->jumlah_rusak_berat ?? 0) }},
                                                    addition_total: {{ (int) ($book->addition_disetujui_total ?? $book->addition_disetujui_total_sum_jumlah_ditambah ?? 0) }},
                                                    cover: '{{ $book->cover_image }}',
                                                    description: '{{ addslashes($book->description ?? 'Tidak ada sinopsis untuk buku ini.') }}'
                                                }; loadBorrowHistory({{ $book->id }})" class="w-6 h-6 rounded bg-blue-50 text-blue-500 border border-blue-100 hover:bg-blue-500 hover:text-white transition flex items-center justify-center" title="Lihat">
                                                    <i class="fas fa-eye text-[9px]"></i>
                                                </button>
                                                <a href="{{ route('admin.stock-addition.create', ['book_id' => $book->id]) }}" class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-500 hover:text-white transition flex items-center justify-center" title="Tambah Stok"><i class="fas fa-plus text-[9px]"></i></a>
                                                <a href="{{ route('admin.books.edit', $book->id) }}" class="w-6 h-6 rounded bg-amber-50 text-amber-500 border border-amber-100 hover:bg-amber-500 hover:text-white transition flex items-center justify-center" title="Edit"><i class="fas fa-edit text-[9px]"></i></a>
                                                <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Hapus buku?')">
                                                @csrf @method('DELETE')
                                                <button class="w-6 h-6 rounded bg-red-50 text-red-500 border border-red-100 hover:bg-red-500 hover:text-white transition flex items-center justify-center" title="Hapus"><i class="fas fa-trash-alt text-[9px]"></i></button>
                                            </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if(isset($books))
                    <div class="p-4 border-t border-gray-50">
                        {{ $books->links() }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4" x-show="selectedBook" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" x-cloak>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm sticky top-4 overflow-hidden">
                    <div class="p-4 border-b border-gray-50 flex justify-between items-center text-gray-800 font-medium">
                        <h2 class="text-sm">Detail Informasi Buku</h2>
                        <button @click="selectedBook = null" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                    </div>
                    <div class="p-5 space-y-6">
                        <div class="text-center space-y-3">
                            <template x-if="selectedBook && selectedBook.cover">
                                <img :src="'/covers/' + selectedBook.cover" class="w-32 h-44 mx-auto rounded-lg shadow-lg object-cover border border-gray-100">
                            </template>
                            <template x-if="!selectedBook || !selectedBook.cover">
                                <div class="w-32 h-44 mx-auto rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 border border-gray-100 shadow-inner">
                                    <i class="fas fa-image text-3xl"></i>
                                </div>
                            </template>
                            <div>
                                <h3 class="text-base font-medium text-gray-800 leading-tight" x-text="selectedBook ? selectedBook.title : ''"></h3>
                                <p class="text-[10px] text-blue-500 font-medium uppercase tracking-widest mt-1" x-text="selectedBook ? selectedBook.author : ''"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="bg-gray-50 p-2 rounded-lg text-center border border-gray-100">
                                <p class="text-[8px] text-gray-400 uppercase font-medium">Stok Awal</p>
                                <p class="text-sm font-medium text-gray-700" x-text="selectedBook ? (parseInt(selectedBook.stock) + selectedBook.dipinjam_count + selectedBook.hilang + selectedBook.rusak_ringan + selectedBook.rusak_sedang + selectedBook.rusak_berat - selectedBook.addition_total) : '0'"></p>
                            </div>
                            <div class="bg-emerald-50 p-2 rounded-lg text-center border border-emerald-100">
                                <p class="text-[8px] text-emerald-500 uppercase font-medium">Stok Tersedia</p>
                                <p class="text-sm font-medium text-emerald-700" x-text="selectedBook ? selectedBook.stock : '0'"></p>
                            </div>
                        </div>

                        <div class="space-y-2 border-t border-gray-50 pt-5">
                            <p class="text-gray-400 font-light uppercase text-[9px] tracking-widest">Keterangan</p>

                            <div class="text-[10px] leading-relaxed space-y-0.5">
                                <template x-if="selectedBook.dipinjam_count <= 0 && selectedBook.hilang <= 0 && (selectedBook.rusak_ringan + selectedBook.rusak_sedang + selectedBook.rusak_berat) <= 0 && selectedBook.addition_total <= 0">
                                    <p class="text-emerald-600">Masih lengkap</p>
                                </template>

                                <template x-if="selectedBook.dipinjam_count > 0">
                                    <p class="text-blue-600" x-text="'Dipinjam ' + selectedBook.dipinjam_count"></p>
                                </template>

                                <template x-if="selectedBook.hilang > 0">
                                    <p class="text-red-600" x-text="'Hilang ' + selectedBook.hilang"></p>
                                </template>

                                <template x-if="selectedBook.rusak_ringan > 0">
                                    <p class="text-orange-500" x-text="'Rusak ringan ' + selectedBook.rusak_ringan"></p>
                                </template>

                                <template x-if="selectedBook.rusak_sedang > 0">
                                    <p class="text-orange-600" x-text="'Rusak sedang ' + selectedBook.rusak_sedang"></p>
                                </template>

                                <template x-if="selectedBook.rusak_berat > 0">
                                    <p class="text-orange-800" x-text="'Rusak berat ' + selectedBook.rusak_berat"></p>
                                </template>

                                <template x-if="selectedBook.addition_total > 0">
                                    <p class="text-emerald-600" x-text="'Tambah stok +' + selectedBook.addition_total"></p>
                                </template>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-gray-50 pt-5">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 font-light uppercase text-[9px]">ISBN</span>
                                <span class="text-gray-700 font-light" x-text="selectedBook ? selectedBook.isbn : '-'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 font-light uppercase text-[9px]">Kategori</span>
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[9px] font-light" x-text="selectedBook ? selectedBook.category : '-'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 font-light uppercase text-[9px]">Lokasi Rak</span>
                                <span class="text-gray-700 font-light" x-text="selectedBook ? selectedBook.rak : '-'"></span>
                            </div>
                        </div>

                        <div class="border-t border-gray-50 pt-5">
                            <p class="text-gray-400 font-light uppercase text-[9px] mb-2 tracking-widest">Sinopsis</p>
                            <p class="text-gray-600 leading-relaxed italic text-justify" x-text="selectedBook ? selectedBook.description : ''"></p>
                        </div>

                        <div class="border-t border-gray-50 pt-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-gray-400 font-light uppercase text-[9px] tracking-widest">Riwayat Peminjaman</p>
                                <span class="text-[9px] text-gray-400" x-show="!borrowHistory.loading" x-text="(borrowHistory.total || 0) + ' kali · ' + (borrowHistory.borrower_count || 0) + ' siswa'"></span>
                            </div>
                            <p class="text-[10px] text-gray-400" x-show="borrowHistory.loading">Memuat riwayat...</p>
                            <template x-if="!borrowHistory.loading && (!borrowHistory.borrowers || borrowHistory.borrowers.length === 0)">
                                <p class="text-[10px] text-gray-400 italic">Belum ada riwayat peminjaman untuk buku ini.</p>
                            </template>
                            <div class="space-y-2 max-h-56 overflow-y-auto" x-show="!borrowHistory.loading && borrowHistory.borrowers && borrowHistory.borrowers.length">
                                <template x-for="(item, idx) in borrowHistory.borrowers" :key="idx">
                                    <div class="flex items-start justify-between gap-2 bg-gray-50 border border-gray-100 rounded-lg px-2.5 py-2">
                                        <div>
                                            <p class="text-[11px] font-medium text-gray-800" x-text="item.name"></p>
                                            <p class="text-[9px] text-gray-400" x-text="(item.nis || '-') + ' · ' + (item.class || '-')"></p>
                                            <p class="text-[9px] text-gray-400 mt-0.5" x-text="'Terakhir: ' + item.last_date + ' (' + item.last_status + ')'"></p>
                                        </div>
                                        <span class="shrink-0 px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[9px] font-medium" x-text="item.count + 'x'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-data="{ 
                            showBarcodeModal: false, 
                            generate() {
                                if (!selectedBook || !selectedBook.isbn || selectedBook.isbn === '-') {
                                    alert('Buku ini tidak memiliki ISBN yang valid untuk dijadikan barcode.');
                                    return;
                                }
                                
                                this.showBarcodeModal = true;
                                
                                this.$nextTick(() => {
                                    JsBarcode('#canvasBarcode', selectedBook.isbn, {
                                        format: 'CODE128',
                                        lineColor: '#1F2937',
                                        width: 2,
                                        height: 60,
                                        displayValue: true,
                                        fontSize: 14
                                    });
                                });
                            },
                            printBarcode() {
                                const svgElement = document.getElementById('canvasBarcode');
                                if (!svgElement) return;
                                
                                const svgBarcode = svgElement.outerHTML;
                                const judul = selectedBook ? selectedBook.title : 'Barcode Buku';
                                
                                const printWin = window.open('', '', 'width=600,height=600');
                                
                                // Menggunakan penggabungan string biasa agar aman di Blade Laravel
                                const htmlContent = '<html>' +
                                    '<head>' +
                                        '<title>Cetak Barcode - ' + judul + '</title>' +
                                        '<style>' +
                                            'body { text-align: center; font-family: Arial, sans-serif; padding-top: 50px; }' +
                                            '.judul { font-size: 14px; font-weight: medium; margin-bottom: 15px; max-width: 300px; margin-left: auto; margin-right: auto; }' +
                                        '</style>' +
                                    '</head>' +
                                    '<body>' +
                                        '<div class=\'judul\'>' + judul + '</div>' +
                                        svgBarcode +
                                        '<script>' +
                                            'setTimeout(function() { window.focus(); window.print(); window.close(); }, 500);' +
                                        '<\/script>' +
                                    '</body>' +
                                '</html>';
                                
                                printWin.document.write(htmlContent);
                                printWin.document.close();
                            }
                        }">
                            <button type="button" @click="generate()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium shadow-md shadow-blue-100 transition-all flex items-center justify-center gap-2 mt-4">
                                <i class="fas fa-barcode text-[10px]"></i> Generate Barcode
                            </button>

                            <div x-show="showBarcodeModal" 
                                style="display: none;" 
                                class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center bg-gray-900 bg-opacity-50 transition-opacity"
                                x-transition:enter="ease-out duration-300" 
                                x-transition:enter-start="opacity-0" 
                                x-transition:enter-end="opacity-100" 
                                x-transition:leave="ease-in duration-200" 
                                x-transition:leave-start="opacity-100" 
                                x-transition:leave-end="opacity-0">
                                
                                <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4 text-center relative" @click.away="showBarcodeModal = false">
                                    <button type="button" @click="showBarcodeModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    
                                    <h3 class="text-lg font-medium text-gray-800 mb-1">Barcode Buku</h3>
                                    <p class="text-[11px] text-gray-500 mb-4" x-text="selectedBook ? selectedBook.title : ''"></p>
                                    
                                    <div class="flex justify-center bg-gray-50 p-5 rounded-lg border border-gray-100 mb-5">
                                        <svg id="canvasBarcode" class="max-w-full"></svg>
                                    </div>
                                    
                                    <button type="button" @click="printBarcode()" class="w-full bg-gray-800 hover:bg-gray-900 text-white py-2 rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2">
                                        <i class="fas fa-print"></i> Cetak Barcode
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</x-layouts.app>
