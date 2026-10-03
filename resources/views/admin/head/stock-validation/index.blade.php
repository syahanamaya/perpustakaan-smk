<x-layouts.app title="Validasi Stok">
    <div class="space-y-6 text-[11px]">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <p class="text-gray-400 uppercase text-[9px]">Menunggu Validasi</p>
                <h3 class="text-base font-medium">{{ $stats['menunggu'] }}</h3>
            </div>
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <p class="text-gray-400 uppercase text-[9px]">Stok Opname</p>
                <h3 class="text-base font-medium text-blue-600">{{ $stats['menunggu_opname'] }}</h3>
            </div>
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <p class="text-gray-400 uppercase text-[9px]">Penambahan Stok</p>
                <h3 class="text-base font-medium text-emerald-600">{{ $stats['menunggu_addition'] }}</h3>
            </div>
        </div>

        <div class="flex flex-wrap justify-between gap-3">
            <div class="flex gap-2">
                <a href="{{ route('head.stock-validation.index') }}" class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('head.stock-validation.index') ? 'bg-blue-600 text-white' : 'bg-white border text-gray-600' }}">Menunggu</a>
                <a href="{{ route('head.stock-validation.history') }}" class="px-4 py-2 rounded-lg font-medium bg-white border text-gray-600">Riwayat</a>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('head.stock-validation.export', ['format' => 'pdf', 'jenis' => 'opname']) }}" class="bg-red-50 text-red-600 px-3 py-2 rounded-lg">PDF Opname</a>
                <a href="{{ route('head.stock-validation.export', ['format' => 'excel', 'jenis' => 'opname']) }}" class="bg-green-50 text-green-600 px-3 py-2 rounded-lg">Excel Opname</a>
                <a href="{{ route('head.stock-validation.export', ['format' => 'pdf', 'jenis' => 'addition']) }}" class="bg-red-50 text-red-600 px-3 py-2 rounded-lg">PDF Tambah Stok</a>
                <a href="{{ route('head.stock-validation.export', ['format' => 'excel', 'jenis' => 'addition']) }}" class="bg-green-50 text-green-600 px-3 py-2 rounded-lg">Excel Tambah Stok</a>
            </div>
        </div>

        <form method="GET" class="bg-white p-4 rounded-xl border flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-gray-400 mb-1">Cari Buku</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="w-48">
                <label class="block text-gray-400 mb-1">Jenis</label>
                <select name="jenis" class="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="">Semua</option>
                    <option value="opname" @selected(request('jenis')=='opname')>Stok Opname</option>
                    <option value="addition" @selected(request('jenis')=='addition')>Penambahan Stok</option>
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
                        <th class="px-4 py-3">Petugas</th>
                        <th class="px-4 py-3">Ringkasan</th>
                        <th class="px-4 py-3">Diajukan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($items as $index => $item)
                        <tr>
                            <td class="px-4 py-3">{{ $items->firstItem() + $index }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] {{ $item->jenis === 'opname' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $item->jenis_label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $item->book->title ?? '-' }}</p>
                                <p class="text-[10px] text-gray-400">{{ $item->book->book_code ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $item->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->ringkasan }}</td>
                            <td class="px-4 py-3">{{ $item->submitted_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ $item->show_url }}" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg">Tinjau</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada pengajuan yang menunggu validasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($items->hasPages())
                <div class="px-4 py-3">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
