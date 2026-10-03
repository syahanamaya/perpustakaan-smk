<x-layouts.app title="Penambahan Stok">
    <div class="space-y-6 text-[11px]">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 uppercase text-[9px]">Total Pengajuan</p>
                <h3 class="text-base font-medium">{{ $stats['total'] }}</h3>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 uppercase text-[9px]">Menunggu Validasi</p>
                <h3 class="text-base font-medium text-amber-600">{{ $stats['menunggu'] }}</h3>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 uppercase text-[9px]">Ditolak</p>
                <h3 class="text-base font-medium text-red-600">{{ $stats['ditolak'] }}</h3>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 uppercase text-[9px]">Disetujui</p>
                <h3 class="text-base font-medium text-green-600">{{ $stats['disetujui'] }}</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form method="GET" action="{{ route('admin.stock-addition.index') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-48">
                    <label class="block text-gray-400 mb-1 font-medium">Cari Buku</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg">
                </div>
                <div class="w-44">
                    <label class="block text-gray-400 mb-1 font-medium">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-white">
                        <option value="">Semua</option>
                        <option value="menunggu_validasi" @selected(request('status')=='menunggu_validasi')>Menunggu Validasi</option>
                        <option value="ditolak" @selected(request('status')=='ditolak')>Ditolak</option>
                        <option value="disetujui" @selected(request('status')=='disetujui')>Disetujui</option>
                    </select>
                </div>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">Filter</button>
            </form>
        </div>

        <div class="flex flex-wrap justify-between gap-3">
            <a href="{{ route('admin.stock-addition.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-plus mr-1"></i> Ajukan Penambahan Stok
            </a>
            <div class="flex gap-2">
                <a href="{{ route('admin.stock-addition.export', ['format' => 'pdf']) }}" class="bg-red-50 text-red-600 px-3 py-2 rounded-lg">PDF</a>
                <a href="{{ route('admin.stock-addition.export', ['format' => 'excel']) }}" class="bg-green-50 text-green-600 px-3 py-2 rounded-lg">Excel</a>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-[10px] uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Buku</th>
                        <th class="px-4 py-3 text-center">Stok Sebelum</th>
                        <th class="px-4 py-3 text-center">Ditambah</th>
                        <th class="px-4 py-3 text-center">Stok Sesudah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($additions as $index => $item)
                        <tr>
                            <td class="px-4 py-3">{{ $additions->firstItem() + $index }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $item->book->title ?? '-' }}</p>
                                <p class="text-[10px] text-gray-400">{{ $item->book->book_code ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">{{ $item->stok_sebelum }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600 font-medium">+{{ $item->jumlah_ditambah }}</td>
                            <td class="px-4 py-3 text-center">{{ $item->stok_sesudah }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $badge = match($item->status) {
                                        'menunggu_validasi' => 'bg-amber-100 text-amber-700',
                                        'ditolak' => 'bg-red-100 text-red-700',
                                        default => 'bg-green-100 text-green-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-[10px] {{ $badge }}">{{ $item->statusLabel() }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $item->submitted_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.stock-addition.show', $item) }}" class="text-blue-600 mr-2"><i class="fas fa-eye"></i></a>
                                @if($item->canEdit())
                                    <a href="{{ route('admin.stock-addition.edit', $item) }}" class="text-amber-600"><i class="fas fa-edit"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada pengajuan penambahan stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($additions->hasPages())
                <div class="px-4 py-3">{{ $additions->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
