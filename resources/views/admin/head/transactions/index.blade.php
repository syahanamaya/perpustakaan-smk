<x-layouts.app title="Riwayat Transaksi">
    <!-- Persiapan Data untuk Alpine.js -->
    @php
        $alpineData = $transactions->map(function($trx) {
            $isLate = $trx->status === 'borrowed' && \Carbon\Carbon::parse($trx->due_date)->isPast();
            $statusLabel = $trx->status === 'returned' ? 'Selesai' : ($isLate ? 'Terlambat' : 'Dipinjam');
            
            return [
                'id' => $trx->id,
                'code' => $trx->transaction_code,
                'student_name' => $trx->student->name ?? '-',
                'student_class' => $trx->student->class ?? 'Kelas -',
                'student_initial' => strtoupper(substr($trx->student->name ?? 'U', 0, 2)),
                'borrow_date' => \Carbon\Carbon::parse($trx->borrow_date)->translatedFormat('d M Y'),
                'due_date' => \Carbon\Carbon::parse($trx->due_date)->translatedFormat('d M Y'),
                'status_label' => $statusLabel,
                'total_fine' => $trx->total_fine ?? 0,
                'details' => $trx->details->map(function($detail) {
                    return [
                        'id' => $detail->id,
                        'title' => $detail->book->title ?? 'Buku Tidak Diketahui',
                        'isbn' => $detail->book->isbn ?? '-',
                    ];
                })
            ];
        })->values();
    @endphp

    <div class="space-y-4" x-data="transactionHistory()">

        <!-- FILTER SECTION -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <form action="{{ route('head.transactions.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4">
                <div class="flex-1 w-full">
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1.5">Cari Anggota / ID / Judul</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." 
                           class="w-full border border-gray-200 rounded-lg text-[11px] py-2 px-3 focus:ring-1 focus:ring-blue-500 bg-gray-50 focus:bg-white outline-none transition-colors">
                </div>
                
                <div class="w-full md:w-48">
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg text-[11px] py-2 px-3 focus:ring-1 focus:ring-blue-500 bg-gray-50 focus:bg-white outline-none transition-colors">
                        <option value="">Semua Status</option>
                        <option value="Selesai" @selected(request('status') == 'Selesai')>Selesai</option>
                        <option value="Terlambat" @selected(request('status') == 'Terlambat')>Terlambat</option>
                        <option value="Dipinjam" @selected(request('status') == 'Dipinjam')>Dipinjam (Aktif)</option>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium text-[11px] transition-colors shadow-sm flex-1 md:flex-none">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('head.transactions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-lg transition-colors flex items-center justify-center">
                        <i class="fas fa-sync-alt text-[11px]"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- MAIN LAYOUT: KIRI (TABEL) & KANAN (DETAIL) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            
            <!-- KOLOM KIRI: TABEL -->
            <div class="lg:col-span-8 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h2 class="text-[13px] font-medium text-gray-800 tracking-tight">Daftar Riwayat Transaksi</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('head.transactions.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="text-[10px] px-3 py-1.5 border border-green-100 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 font-medium inline-block">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                        <a href="{{ route('head.transactions.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="text-[10px] px-3 py-1.5 border border-red-100 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 font-medium inline-block">
                            <i class="fas fa-file-pdf mr-1"></i> Export PDF
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[11px]">
                        <thead class="bg-gray-50/50 text-gray-400 text-[10px] uppercase tracking-wider font-medium border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3">ID Transaksi</th>
                                <th class="px-5 py-3">Anggota</th>
                                <th class="px-5 py-3">Tgl Pinjam</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Denda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-gray-700">
                            @forelse($transactions as $trx)
                                <tr @click="selectTransaction({{ $trx->id }})" 
                                    class="hover:bg-blue-50/40 cursor-pointer transition-colors"
                                    :class="{ 'bg-blue-50/40': selectedTrx && selectedTrx.id === {{ $trx->id }} }">
                                    <td class="px-5 py-3.5 font-medium text-blue-600">{{ $trx->transaction_code }}</td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-gray-800">{{ $trx->student->name ?? '-' }}</p>
                                        <p class="text-[9px] text-gray-400">{{ $trx->student->class ?? '-' }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 font-medium">{{ \Carbon\Carbon::parse($trx->borrow_date)->translatedFormat('d M Y') }}</td>
                                    
                                    <td class="px-5 py-3.5 text-center">
                                        @php
                                            $isLate = $trx->status === 'borrowed' && \Carbon\Carbon::parse($trx->due_date)->isPast();
                                        @endphp
                                        @if($trx->status === 'returned')
                                            <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-2 py-0.5 rounded text-[10px] font-medium">Selesai</span>
                                        @elseif($isLate)
                                            <span class="bg-orange-50 text-orange-600 border border-orange-100 px-2 py-0.5 rounded text-[10px] font-medium">Terlambat</span>
                                        @else
                                            <span class="bg-blue-50 text-blue-600 border border-blue-100 px-2 py-0.5 rounded text-[10px] font-medium">Dipinjam</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-5 py-3.5 text-right font-medium text-gray-800">
                                        Rp {{ number_format($trx->total_fine ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-gray-400 font-medium text-[11px]">Tidak ada data transaksi ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/30">
                        {{ $transactions->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>

            <!-- KOLOM KANAN: DETAIL PANEL STICKY -->
            <div class="lg:col-span-4 relative">
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-5 sticky top-5 min-h-100">
                    
                    <!-- State if nothing is selected -->
                    <div x-show="!selectedTrx" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 bg-white/80 rounded-xl z-10 backdrop-blur-[1px]">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-hand-pointer text-gray-400 text-lg"></i>
                        </div>
                        <p class="text-gray-500 font-medium text-[12px]">Pilih transaksi di tabel<br>untuk melihat detail</p>
                    </div>

                    <!-- Panel Content -->
                    <template x-if="selectedTrx">
                        <div class="space-y-5 animate-fade-in">
                            <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                                <h3 class="font-medium text-gray-800 text-[13px]">Detail Transaksi</h3>
                                <span class="text-[10px] font-medium text-gray-400" x-text="selectedTrx.code"></span>
                            </div>

                            <!-- Profil Info -->
                            <div class="bg-blue-50/40 border border-blue-100/50 rounded-lg p-3 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium text-[12px] shadow-sm">
                                    <span x-text="selectedTrx.student_initial"></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-[12px]" x-text="selectedTrx.student_name"></p>
                                    <p class="text-[10px] text-gray-500 mt-0.5" x-text="selectedTrx.student_class"></p>
                                </div>
                            </div>

                            <!-- Detail Tanggal & Status -->
                            <div class="space-y-3 text-[11px] text-gray-600 border-b border-gray-100 pb-4">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-500">Tanggal Pinjam</span>
                                    <span class="font-medium text-gray-800" x-text="selectedTrx.borrow_date"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-500">Batas Kembali</span>
                                    <span class="font-medium text-gray-800" x-text="selectedTrx.due_date"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-500">Status</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium"
                                          :class="{
                                              'bg-emerald-50 text-emerald-600': selectedTrx.status_label === 'Selesai',
                                              'bg-orange-50 text-orange-600': selectedTrx.status_label === 'Terlambat',
                                              'bg-blue-50 text-blue-600': selectedTrx.status_label === 'Dipinjam'
                                          }" x-text="selectedTrx.status_label">
                                    </span>
                                </div>
                                <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-50">
                                    <span class="font-medium text-gray-500">Total Denda</span>
                                    <span class="font-medium text-red-600 text-[13px]" x-text="formatRupiah(selectedTrx.total_fine)"></span>
                                </div>
                            </div>

                            <!-- Daftar Buku -->
                            <div>
                                <p class="text-[10px] font-medium text-gray-800 uppercase tracking-wider mb-3">Daftar Buku</p>
                                <div class="space-y-2 max-h-37.5 overflow-y-auto pr-1 custom-scrollbar">
                                    <template x-for="book in selectedTrx.details" :key="book.id">
                                        <div class="bg-gray-50 border border-gray-100 p-2.5 rounded-lg flex justify-between items-center gap-3">
                                            <div class="flex items-center gap-2.5 overflow-hidden">
                                                <div class="w-6 h-8 bg-gray-200 rounded shrink-0"></div>
                                                <div class="truncate">
                                                    <p class="font-medium text-gray-700 text-[11px] truncate" x-text="book.title"></p>
                                                    <p class="text-[9px] text-gray-400 mt-0.5" x-text="book.isbn"></p>
                                                </div>
                                            </div>
                                            <!-- Jika ada perincian denda per buku, bisa ditampilkan di sini, sementara set 0 atau sesuaikan backend -->
                                            <!-- <span class="text-[10px] font-medium text-red-500 shrink-0">Rp 0</span> -->
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Info Note -->
                            <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-3 text-center mt-4">
                                <p class="text-[10px] text-blue-700 font-medium italic leading-relaxed">
                                    "Pastikan denda sudah lunas sebelum mengizinkan peminjaman berikutnya."
                                </p>
                            </div>

                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- Script Alpine.js -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('transactionHistory', () => ({
                transactions: {!! json_encode($alpineData) !!},
                selectedTrx: null,

                init() {
                    // Otomatis pilih transaksi pertama jika ada
                    if (this.transactions.length > 0) {
                        this.selectedTrx = this.transactions[0];
                    }
                },

                selectTransaction(id) {
                    this.selectedTrx = this.transactions.find(t => t.id === id);
                },

                formatRupiah(angka) {
                    return "Rp " + new Intl.NumberFormat("id-ID").format(angka || 0);
                }
            }))
        })
    </script>
    
    <style>
        /* Animasi Transisi & Scrollbar tipis untuk panel kanan */
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</x-layouts.app>