<x-layouts.app title="Riwayat Validasi Stok">
    <div class="space-y-6 text-[11px]">
        <div class="flex gap-2">
            <a href="{{ route('head.stock-validation.index') }}" class="px-4 py-2 rounded-lg font-medium bg-white border text-gray-600">Menunggu</a>
            <a href="{{ route('head.stock-validation.history') }}" class="px-4 py-2 rounded-lg font-medium bg-blue-600 text-white">Riwayat</a>
        </div>
        <form method="GET" class="bg-white p-4 rounded-xl border flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-gray-400 mb-1">Cari Buku</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="w-44">
                <label class="block text-gray-400 mb-1">Jenis</label>
                <select name="jenis" class="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="">Semua</option>
                    <option value="opname" @selected(request('jenis')=='opname')>Stok Opname</option>
                    <option value="addition" @selected(request('jenis')=='addition')>Penambahan Stok</option>
                </select>
            </div>
            <div class="w-44">
                <label class="block text-gray-400 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="">Semua</option>
                    <option value="disetujui" @selected(request('status')=='disetujui')>Disetujui</option>
                    <option value="ditolak" @selected(request('status')=='ditolak')>Ditolak</option>
                </select>
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">Filter</button>
        </form>
        <div class="bg-white border rounded-xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-[10px] uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Buku</th>
                        <th class="px-4 py-3">Ringkasan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Divalidasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($items as $index => $item)
                        <tr>
                            <td class="px-4 py-3">{{ $items->firstItem() + $index }}</td>
                            <td class="px-4 py-3">{{ $item->jenis_label }}</td>
                            <td class="px-4 py-3">{{ $item->book->title ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->ringkasan }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] {{ $item->status === 'disetujui' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $item->status_label }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $item->validated_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($items->hasPages())
                <div class="px-4 py-3">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
