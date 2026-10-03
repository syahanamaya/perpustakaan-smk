<x-layouts.app title="Manajemen Rak">
    <div x-data="{ 
        modalOpen: false, 
        isEdit: false, 
        formAction: '', 
        formTitle: '', 
        rakId: '',
        rakCode: '',
        rakName: '',
        rakLocation: '',
        rakCapacity: '',
        openMenuId: null,

        openCreateModal() {
            this.modalOpen = true;
            this.isEdit = false;
            this.formTitle = 'Tambah Rak Baru';
            this.formAction = '{{ route('admin.raks.store') }}';
            this.rakId = '';
            this.rakCode = '';
            this.rakName = '';
            this.rakLocation = '';
            this.rakCapacity = '';
        },

        openEditModal(id, code, name, location, capacity) {
            this.modalOpen = true;
            this.isEdit = true;
            this.formTitle = 'Edit Data Rak';
            this.formAction = '/admin/raks/' + id; 
            this.rakId = id;
            this.rakCode = code;
            this.rakName = name;
            this.rakLocation = location;
            this.rakCapacity = capacity;
            this.openMenuId = null;
        }
    }" class="space-y-6 text-[11px] text-gray-600">

        <!-- 1. STATISTIC CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-lg"><i class="fas fa-layer-group"></i></div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium">Total Rak</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $total_rak }}</h3>
                    <p class="text-[9px] text-gray-400">Semua rak</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex items-center justify-center text-lg"><i class="fas fa-check-double"></i></div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium">Rak Terisi</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $rak_terisi_count }}</h3>
                    <p class="text-[9px] text-gray-400">Rak dengan koleksi buku</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center text-lg"><i class="fas fa-flag"></i></div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium">Rak Kosong</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $rak_kosong_count }}</h3>
                    <p class="text-[9px] text-gray-400">Tersedia untuk koleksi baru</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-500 flex items-center justify-center text-lg"><i class="fas fa-book"></i></div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium">Total Buku</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($total_buku, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Jumlah fisik buku</p>
                </div>
            </div>
        </div>

        <!-- 2. FILTER BAR -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form action="{{ route('admin.raks.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-50">
                    <label class="block text-gray-400 font-medium mb-1">Cari Rak</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kode atau nama rak..." class="w-full pl-3 pr-10 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-300"></i>
                    </div>
                </div>
                <div class="w-40">
                    <label class="block text-gray-400 font-medium mb-1">Lokasi</label>
                    <select name="lokasi" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="Semua Lokasi">Semua Lokasi</option>
                        @foreach($distribusi_lokasi as $dl)
                            <option value="{{ $dl->lokasi }}" {{ request('lokasi') == $dl->lokasi ? 'selected' : '' }}>{{ $dl->lokasi }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fas fa-filter text-[10px]"></i> Terapkan Filter
                </button>
                <a href="{{ route('admin.raks.index') }}" class="border border-gray-200 text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-100 transition flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Reset
                </a>
            </form>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- 3. MAIN TABLE -->
            <div class="col-span-12 lg:col-span-8 space-y-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50 flex justify-between items-center text-gray-800">
                        <h2 class="text-sm font-medium">Daftar Rak <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-[10px] text-gray-400 font-normal">{{ $raks->total() }} data</span></h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-[9px] text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-3 font-medium">No.</th>
                                    <th class="px-4 py-3 font-medium">Kode Rak</th>
                                    <th class="px-4 py-3 font-medium">Nama Rak</th>
                                    <th class="px-4 py-3 font-medium">Lokasi</th>
                                    <th class="px-4 py-3 font-medium text-center">Kapasitas</th>
                                    <th class="px-4 py-3 font-medium text-center">Terisi</th>
                                    <th class="px-4 py-3 font-medium">Persentase</th>
                                    <th class="px-4 py-3 font-medium text-center w-10">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($raks as $index => $rak)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-400">{{ $raks->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-500 uppercase">{{ $rak->kode_rak }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $rak->nama_rak }}</td>
                                    <td class="px-4 py-3">{{ $rak->lokasi }}</td>
                                    <td class="px-4 py-3 text-center">{{ $rak->kapasitas }}</td>
                                    <td class="px-4 py-3 text-center">{{ $rak->terisi }}</td>
                                    <td class="px-4 py-3 w-32">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $rak->persentase > 90 ? 'bg-red-500' : ($rak->persentase > 50 ? 'bg-green-500' : 'bg-orange-400') }}" 
                                                     style="width: {{ $rak->persentase }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-medium">{{ $rak->persentase }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center relative">
                                        <button @click="openMenuId === {{ $rak->id }} ? openMenuId = null : openMenuId = {{ $rak->id }}" 
                                                class="text-gray-300 hover:text-gray-600 transition p-2">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        
                                        <div x-show="openMenuId === {{ $rak->id }}" @click.away="openMenuId = null" 
                                             class="absolute right-10 top-2 w-28 bg-white border border-gray-100 shadow-xl rounded-lg z-50 overflow-hidden">
                                            <button @click="openEditModal({{ $rak->id }}, '{{ $rak->kode_rak }}', '{{ addslashes($rak->nama_rak) }}', '{{ addslashes($rak->lokasi) }}', '{{ $rak->kapasitas }}')" 
                                                    class="w-full text-left px-4 py-2 hover:bg-amber-50 text-amber-600 flex items-center gap-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form action="{{ route('admin.raks.destroy', $rak->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-red-50 text-red-600 flex items-center gap-2" onclick="return confirm('Hapus rak ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="p-10 text-center text-gray-400 italic">Tidak ada data rak.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 bg-white border-t border-gray-50">
                        {{ $raks->links() }}
                    </div>
                </div>
            </div>

            <!-- 4. SIDEBAR -->
            <div class="col-span-12 lg:col-span-4 space-y-6">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50">
                        <h2 class="text-sm font-medium text-gray-800">Tambah Rak Baru</h2>
                    </div>
                    <form action="{{ route('admin.raks.store') }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Kode Rak <span class="text-red-500">*</span></label>
                            <input type="text" name="kode_rak" required placeholder="Contoh: RAK-A01" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Nama Rak <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_rak" required placeholder="Contoh: Rak Novel" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Lokasi <span class="text-red-500">*</span></label>
                            <input type="text" name="lokasi" required placeholder="Contoh: Ruang Utama" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Kapasitas (Buku) <span class="text-red-500">*</span></label>
                            <input type="number" name="kapasitas" required placeholder="Contoh: 100" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 shadow-md transition flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Rak
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-medium text-gray-800 mb-6">Distribusi Rak per Lokasi</h2>
                    <div class="space-y-3">
                        @foreach($distribusi_lokasi as $lok)
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> 
                                <span>{{ $lok->lokasi }}</span>
                            </div>
                            <span class="font-light">{{ $lok->total }} Rak</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL EDIT -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-cloak>
            <div class="bg-white rounded-xl w-full max-w-md shadow-2xl overflow-hidden" @click.away="modalOpen = false">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-sm font-medium text-gray-800" x-text="formTitle"></h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form :action="formAction" method="POST" class="p-5 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Kode Rak</label>
                        <input type="text" name="kode_rak" x-model="rakCode" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Nama Rak</label>
                        <input type="text" name="nama_rak" x-model="rakName" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Lokasi</label>
                        <input type="text" name="lokasi" x-model="rakLocation" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Kapasitas</label>
                        <input type="number" name="kapasitas" x-model="rakCapacity" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 font-medium text-gray-400">Batal</button>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium shadow-md">Update Rak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>