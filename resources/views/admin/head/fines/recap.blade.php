<x-layouts.app title="Rekap Denda">
    <div class="space-y-5">
        
        {{-- Filter Section --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <form action="{{ route('head.fines.recap') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-50">
                    <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Cari Anggota</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau NIS..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] outline-none focus:border-blue-500">
                </div>
                <div class="w-40">
                    <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Dari</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] outline-none focus:border-blue-500">
                </div>
                <div class="w-40">
                    <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Sampai</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] outline-none focus:border-blue-500">
                </div>
                <div class="w-44">
                    <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] outline-none bg-white">
                        <option value="">Semua Status</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Dibayar</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[11px] font-medium hover:bg-blue-700">Terapkan Filter</button>
                    <a href="{{ route('head.fines.recap') }}" class="px-3 py-2 bg-gray-50 text-gray-400 border border-gray-200 rounded-lg hover:bg-gray-100 flex items-center"><i class="fas fa-sync-alt text-[11px]"></i></a>
                </div>
            </form>
        </div>

        {{-- Card Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex justify-center items-center text-lg"><i class="fas fa-money-bill-wave"></i></div>
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-medium tracking-tight">Total Denda</p>
                    <h3 class="text-[14px] font-medium text-gray-800">Rp {{ number_format($total_fine, 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex justify-center items-center text-lg"><i class="fas fa-check-circle"></i></div>
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-medium tracking-tight">Denda Dibayar</p>
                    <h3 class="text-[14px] font-medium text-gray-800">Rp {{ number_format($denda_dibayar, 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex justify-center items-center text-lg"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-medium tracking-tight">Belum Dibayar</p>
                    <h3 class="text-[14px] font-medium text-gray-800">Rp {{ number_format($denda_belum, 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex justify-center items-center text-lg"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-medium tracking-tight">Total Transaksi</p>
                    <h3 class="text-[14px] font-medium text-gray-800">{{ $total_transaksi_denda }}</h3>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex justify-center items-center text-lg"><i class="fas fa-calculator"></i></div>
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-medium tracking-tight">Rata-rata</p>
                    <h3 class="text-[14px] font-medium text-gray-800">Rp {{ number_format($rata_rata_denda, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex justify-center items-center text-lg"><i class="fas fa-book"></i></div>
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-medium tracking-tight">Buku Rusak (Data Buku)</p>
                    <h3 class="text-[14px] font-medium text-gray-800">{{ $stok_rusak ?? 0 }} eksemplar</h3>
                    <p class="text-[9px] text-gray-400">Denda rusak dari harga buku: Rp {{ number_format($denda_kerusakan, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-slate-50 text-slate-600 flex justify-center items-center text-lg"><i class="fas fa-search"></i></div>
                <div>
                    <p class="text-[9px] text-gray-400 uppercase font-medium tracking-tight">Buku Hilang (Data Buku)</p>
                    <h3 class="text-[14px] font-medium text-gray-800">{{ $stok_hilang ?? 0 }} eksemplar</h3>
                    <p class="text-[9px] text-gray-400">Denda hilang 100% harga buku: Rp {{ number_format($denda_kehilangan, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header & Action Buttons -->
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white font-medium">
            <h2 class="text-[13px] font-bold text-gray-800 tracking-tight">Daftar Rekap Denda</h2>
            <div class="flex gap-2">
                <!-- Export Excel -->
                <a href="{{ route('head.reports.export', array_merge(request()->query(), ['type' => 'denda', 'format' => 'excel'])) }}" 
                class="text-[10px] px-3 py-1.5 border border-green-100 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 font-medium inline-block">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </a>

                <!-- Export PDF -->
                <a href="{{ route('head.reports.export', array_merge(request()->query(), ['type' => 'denda', 'format' => 'pdf'])) }}" 
                class="text-[10px] px-3 py-1.5 border border-red-100 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 font-medium inline-block">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Table Body -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-[10px] text-gray-400 uppercase tracking-wider font-medium">
                    <tr>
                        <th class="px-5 py-3">No.</th>
                        <th class="px-5 py-3">Anggota</th>
                        <th class="px-5 py-3">Keterangan</th>
                        <th class="px-5 py-3 text-right">Total Denda</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-[11px] text-gray-600 divide-y divide-gray-50">
                    @forelse($fines as $index => $fine)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3">{{ $fines->firstItem() + $index }}</td>
                        <td class="px-5 py-3 font-medium">
                            <div class="font-bold text-gray-800">{{ $fine->student->name ?? 'N/A' }}</div>
                            <div class="text-[9px] text-gray-400">{{ $fine->student->nis ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            {{-- Kamu bisa mengambil keterangan dinamis dari DB jika ada kolomnya --}}
                            {{ $fine->description ?? 'Keterlambatan Pengembalian' }}
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-700">
                            Rp {{ number_format($fine->fine->total_fine, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($fine->fine->fine_status == 'paid')
                                <span class="px-2 py-0.5 bg-green-50 text-green-600 rounded text-[9px] font-medium border border-green-100">Dibayar</span>
                            @else
                                <span class="px-2 py-0.5 bg-red-50 text-red-500 rounded text-[9px] font-medium border border-red-100">Belum Dibayar</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-gray-400 italic">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-2xl mb-2"></i>
                                <span>Data denda tidak ditemukan.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-50 bg-white">
            {{ $fines->appends(request()->query())->links() }}
        </div>
    </div>
    </div>
</x-layouts.app>