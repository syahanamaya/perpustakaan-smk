<x-layouts.app title="Riwayat Validasi Opname">
    <div class="space-y-6 text-[11px]">

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
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form action="{{ route('head.stock-opname.history') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-48">
                    <label class="block text-gray-400 mb-1 font-medium">Cari Buku</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul atau kode buku..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px]">
                </div>
                <div class="w-44">
                    <label class="block text-gray-400 mb-1 font-medium">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-white text-[11px]">
                        <option value="">Semua</option>
                        <option value="disetujui" @selected(request('status') == 'disetujui')>Disetujui</option>
                        <option value="ditolak" @selected(request('status') == 'ditolak')>Ditolak</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">Filter</button>
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
                            <th class="px-4 py-3 text-center">Selisih</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3">Divalidasi</th>
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
                                <td class="px-4 py-3 text-center">{{ $opname->selisihLabel() }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $badge = $opname->status === 'disetujui' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-[10px] font-medium {{ $badge }}">{{ $opname->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $opname->validated_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat validasi.</td>
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
