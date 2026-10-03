<x-layouts.app title="Validasi Stok Opname">
    <div class="space-y-6 text-[11px]">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Menunggu Validasi</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $stats['menunggu'] }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-lg"><i class="fas fa-check-circle"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Disetujui Bulan Ini</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $stats['disetujui_bulan_ini'] }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-lg"><i class="fas fa-times-circle"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Ditolak Bulan Ini</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $stats['ditolak_bulan_ini'] }}</h3>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('head.stock-opname.index') }}"
                    class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('head.stock-opname.index') ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">
                    Menunggu Validasi
                </a>
                <a href="{{ route('head.stock-opname.history') }}"
                    class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('head.stock-opname.history') ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">
                    Riwayat
                </a>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-gray-500">Export laporan:</span>
                <a href="{{ route('head.stock-opname.export', ['format' => 'pdf']) }}"
                    class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-2 rounded-lg font-medium flex items-center gap-1.5">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('head.stock-opname.export', ['format' => 'excel']) }}"
                    class="bg-green-50 text-green-600 hover:bg-green-100 px-3 py-2 rounded-lg font-medium flex items-center gap-1.5">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form action="{{ route('head.stock-opname.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-48">
                    <label class="block text-gray-400 mb-1 font-medium">Cari Buku</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul atau kode buku..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px]">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">Cari</button>
            </form>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[11px]">
                    <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-wider font-medium border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">No.</th>
                            <th class="px-4 py-3">Buku</th>
                            <th class="px-4 py-3">Petugas</th>
                            <th class="px-4 py-3 text-center">Stok Fisik</th>
                            <th class="px-4 py-3 text-center">Selisih</th>
                            <th class="px-4 py-3">Diajukan</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        @forelse($opnames as $index => $opname)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 text-center">{{ $opnames->firstItem() + $index }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $opname->book->title ?? '-' }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $opname->book->book_code ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $opname->user->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $opname->stok_fisik }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="{{ $opname->selisih === 0 ? 'text-green-600' : 'text-amber-600' }} font-medium">{{ $opname->selisihLabel() }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $opname->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('head.stock-opname.show', $opname) }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg font-medium inline-flex items-center gap-1">
                                        <i class="fas fa-search"></i> Tinjau
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada pengajuan yang menunggu validasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($opnames->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $opnames->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
