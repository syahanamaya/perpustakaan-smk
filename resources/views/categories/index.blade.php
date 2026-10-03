<x-layouts.app title="Manajemen Kategori">
    <div x-data="{ 
        modalOpen: false, 
        isEdit: false, 
        formAction: '', 
        formTitle: '', 
        categoryId: '',
        categoryName: '',
        categoryDescription: '',
        categoryStatus: 'Aktif',
        openMenuId: null, // Untuk melacak menu titik tiga yang terbuka

        openEditModal(id, name, description, status) {
            this.modalOpen = true;
            this.isEdit = true;
            this.formTitle = 'Edit Kategori';
            this.formAction = '/admin/categories/' + id; 
            this.categoryId = id;
            this.categoryName = name;
            this.categoryDescription = description;
            this.categoryStatus = status;
            this.openMenuId = null;
        }
    }" class="space-y-6 text-[11px]">

        <!-- STATISTIC CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg"><i class="fas fa-folder"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Total Kategori</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $totalCategories }}</h3>
                    <p class="text-[9px] text-gray-400">Semua kategori</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-lg"><i class="fas fa-book"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Total Buku</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($totalBooks) }}</h3>
                    <p class="text-[9px] text-gray-400">Buku dalam semua kategori</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-lg"><i class="fas fa-check-circle"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Kategori Aktif</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $activeCategories }}</h3>
                    <p class="text-[9px] text-gray-400">Status aktif saat ini</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-lg"><i class="fas fa-times-circle"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Kategori Nonaktif</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $inactiveCategories }}</h3>
                    <p class="text-[9px] text-gray-400">Kategori dinonaktifkan</p>
                </div>
            </div>
        </div>

        <!-- FILTER BAR -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form action="{{ route('admin.categories.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-50">
                    <label class="block text-gray-400 mb-1 font-medium">Cari Kategori</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama kategori..." 
                            class="w-full pl-3 pr-10 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-[11px]">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-300"></i>
                    </div>
                </div>
                
                <div class="w-48">
                    <label class="block text-gray-400 mb-1 font-medium">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="">Semua Status</option>
                        <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Nonaktif" {{ request('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="w-48">
                    <label class="block text-gray-400 mb-1 font-medium">Urutkan</label>
                    <select name="sort" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>Nama (A - Z)</option>
                        <option value="za" {{ request('sort') == 'za' ? 'selected' : '' }}>Nama (Z - A)</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2 text-[11px]">
                        <i class="fas fa-filter text-[10px]"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="border border-gray-200 text-gray-500 px-6 py-2 rounded-lg font-medium hover:bg-gray-50 transition flex items-center gap-2 text-[11px]">
                        <i class="fas fa-sync-alt text-[10px]"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- 3. MAIN TABLE (Kiri) -->
            <div class="col-span-12 lg:col-span-8 space-y-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                        <h2 class="text-sm font-medium text-gray-800">Daftar Kategori <span class="ml-2 text-[10px] text-gray-400 font-light bg-gray-50 px-2 py-0.5 rounded-full">{{ $categories->total() }} data</span></h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-gray-400 uppercase text-[9px] tracking-wider border-b border-gray-50">
                                    <th class="px-4 py-3 font-medium">No.</th>
                                    <th class="px-4 py-3 font-medium">Nama Kategori</th>
                                    <th class="px-4 py-3 font-medium">Deskripsi</th>
                                    <th class="px-4 py-3 font-medium text-center">Jumlah Buku</th>
                                    <th class="px-4 py-3 font-medium text-center">Status</th>
                                    <th class="px-4 py-3 font-medium text-center w-10">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($categories as $index => $category)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-400">{{ $categories->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-700">{{ $category->name }}</td>
                                    <td class="px-4 py-3 text-gray-500 italic">{{ Str::limit($category->description ?? 'Tidak ada deskripsi', 30) }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $category->books_count ?? 0 }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($category->status == 'Aktif')
                                            <span class="px-2 py-0.5 rounded-md bg-green-50 text-green-600 border border-green-100 text-[9px] font-medium">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-red-50 text-red-600 border border-red-100 text-[9px] font-medium">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center relative">
                                        <!-- Ellipsis Menu Button -->
                                        <button @click="openMenuId === {{ $category->id }} ? openMenuId = null : openMenuId = {{ $category->id }}" 
                                                class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 transition">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div x-show="openMenuId === {{ $category->id }}" 
                                             @click.away="openMenuId = null"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             class="absolute right-10 top-2 w-32 bg-white border border-gray-100 shadow-xl rounded-lg z-50 overflow-hidden">
                                            <button @click="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', '{{ $category->status }}')" 
                                                    class="w-full text-left px-4 py-2 hover:bg-amber-50 text-gray-600 flex items-center gap-2 transition">
                                                <i class="fas fa-edit text-amber-500"></i> Edit
                                            </button>
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Hapus kategori?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-red-50 text-red-500 flex items-center gap-2 transition">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-50 flex justify-between items-center bg-white">
                        <p class="text-gray-400">Menampilkan {{ $categories->firstItem() }} - {{ $categories->lastItem() }} dari {{ $categories->total() }} data</p>
                        {{ $categories->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>

            <!-- 4. SIDEBAR (Kanan) -->
            <div class="col-span-12 lg:col-span-4 space-y-6">
                <!-- Form Tambah -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50 bg-white">
                        <h2 class="text-sm font-medium text-gray-800">Tambah Kategori Baru</h2>
                    </div>
                    <form action="{{ route('admin.categories.store') }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-gray-700 font-medium mb-1.5">Nama Kategori</label>
                            <input type="text" name="name" placeholder="Masukkan nama kategori" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1.5">Deskripsi</label>
                            <textarea name="description" placeholder="Masukkan deskripsi kategori (opsional)" rows="3"
                                      class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-400 font-medium mb-1.5">Status</label>
                            <select class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-gray-50">
                                <option>Aktif</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2 shadow-md">
                            <i class="fas fa-save"></i> Simpan Kategori
                        </button>
                    </form>
                </div>

                <!-- Kategori Terpopuler -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50">
                        <h2 class="text-sm font-medium text-gray-800">Kategori dengan Buku Terbanyak</h2>
                    </div>
                    <div class="p-2">
                        @foreach($topCategories ?? [] as $top)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                                    <i class="fas fa-folder text-[12px]"></i>
                                </div>
                                <span class="font-medium text-gray-700">{{ $top?->name }}</span>
                            </div>
                            <span class="text-gray-400 font-medium">{{ $top?->books_count }} buku</span>
                        </div>
                        @endforeach
                    </div>
                    <button class="w-full p-4 text-center text-blue-600 border-t border-gray-50 hover:bg-blue-50 transition font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-eye text-[10px]"></i> Lihat Semua Kategori
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL EDIT (Muncul saat Klik Edit di Ellipsis) -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-cloak>
            <div class="bg-white rounded-xl w-full max-w-md shadow-2xl overflow-hidden animate-fade-in-down">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-sm font-medium text-gray-800" x-text="formTitle"></h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form :action="formAction" method="POST" class="p-5 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-gray-700 font-medium mb-1.5">Nama Kategori</label>
                        <input type="text" name="name" x-model="categoryName" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1.5">Deskripsi</label>
                        <textarea name="description" x-model="categoryDescription" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1.5">Status</label>
                        <select name="status" x-model="categoryStatus" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-gray-500 font-medium">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium">Update Data</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>