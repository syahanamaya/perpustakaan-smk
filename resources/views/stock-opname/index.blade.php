<x-layouts.app title="Stok Opname">
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

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Total Pengajuan</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $stats['total'] }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Menunggu Validasi</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $stats['menunggu'] }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-lg"><i class="fas fa-times-circle"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Ditolak</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $stats['ditolak'] }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-lg"><i class="fas fa-check-circle"></i></div>
                <div>
                    <p class="text-gray-400 font-light uppercase text-[9px]">Disetujui</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $stats['disetujui'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form action="{{ route('admin.stock-opname.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-48">
                    <label class="block text-gray-400 mb-1 font-medium">Cari Buku</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul atau kode buku..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-[11px]">
                </div>
                <div class="w-44">
                    <label class="block text-gray-400 mb-1 font-medium">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-white text-[11px]">
                        <option value="">Semua Status</option>
                        <option value="menunggu_validasi" @selected(request('status') == 'menunggu_validasi')>Menunggu Validasi</option>
                        <option value="ditolak" @selected(request('status') == 'ditolak')>Ditolak</option>
                        <option value="disetujui" @selected(request('status') == 'disetujui')>Disetujui</option>
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-gray-400 mb-1 font-medium">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px]">
                </div>
                <div class="w-36">
                    <label class="block text-gray-400 mb-1 font-medium">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px]">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">Filter</button>
                <a href="{{ route('admin.stock-opname.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg font-medium">Reset</a>
            </form>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.stock-opname.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                <i class="fas fa-plus"></i> Ajukan Stok Opname
            </a>

            <div class="flex items-center gap-2">
                <span class="text-gray-500">Export (hanya disetujui):</span>
                <a href="{{ route('admin.stock-opname.export', array_merge(request()->only(['start_date', 'end_date']), ['format' => 'pdf'])) }}"
                    class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-2 rounded-lg font-medium flex items-center gap-1.5">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('admin.stock-opname.export', array_merge(request()->only(['start_date', 'end_date']), ['format' => 'excel'])) }}"
                    class="bg-green-50 text-green-600 hover:bg-green-100 px-3 py-2 rounded-lg font-medium flex items-center gap-1.5">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[11px]">
                    <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-wider font-medium border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">No.</th>
                            <th class="px-4 py-3">Buku</th>
                            <th class="px-4 py-3 text-center">Stok Baik</th>
                            <th class="px-4 py-3">Temuan Kondisi</th>
                            <th class="px-4 py-3 text-center">Selisih</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3">Tanggal</th>
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
                                <td class="px-4 py-3 text-center">{{ $opname->stok_fisik }}</td>
                                <td class="px-4 py-3 text-[10px] text-gray-600">{{ $opname->kondisiRingkasan() }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php $color = $opname->selisih === 0 ? 'text-green-600' : 'text-amber-600'; @endphp
                                    <span class="{{ $color }} font-medium">{{ $opname->selisihLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $badge = match($opname->status) {
                                            'menunggu_validasi' => 'bg-amber-100 text-amber-700',
                                            'ditolak' => 'bg-red-100 text-red-700',
                                            'disetujui' => 'bg-green-100 text-green-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-[10px] font-medium {{ $badge }}">{{ $opname->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $opname->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.stock-opname.show', $opname) }}" class="text-blue-600 hover:text-blue-800" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($opname->canEdit())
                                            <a href="{{ route('admin.stock-opname.edit', $opname) }}" class="text-amber-600 hover:text-amber-800" title="Perbaiki">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada pengajuan stok opname.</td>
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
