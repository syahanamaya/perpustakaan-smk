<x-layouts.app title="Denda - Manajemen Perpustakaan">
    @php
        // Kalkulasi Summary Card (Gunakan relasi ->fine)
        $totalDendaKeseluruhan = $fines->sum(fn($f) => abs($f->fine->total_fine ?? 0));
        
        // Gunakan filter() karena kita mengecek relasi di dalam collection
        $dendaDibayar = $fines->filter(fn($f) => ($f->fine->fine_status ?? '') === 'paid')
                              ->sum(fn($f) => abs($f->fine->total_fine ?? 0));
                              
        $dendaBelumDibayar = $totalDendaKeseluruhan - $dendaDibayar;
        
        $persenDibayar = $totalDendaKeseluruhan > 0 ? round(($dendaDibayar / $totalDendaKeseluruhan) * 100) : 0;
        $persenBelum = $totalDendaKeseluruhan > 0 ? round(($dendaBelumDibayar / $totalDendaKeseluruhan) * 100) : 0;

        // Persiapan Data untuk Alpine.js
        $alpineFines = $fines->map(function($fine) {
            $dueDate = \Carbon\Carbon::parse($fine->due_date)->startOfDay();
            $returnDate = $fine->return_date ? \Carbon\Carbon::parse($fine->return_date)->startOfDay() : now()->startOfDay();
            
            $lateDays = $returnDate->isAfter($dueDate) ? abs($returnDate->diffInDays($dueDate)) : 0;
            $bookCount = $fine->details ? $fine->details->count() : 1;
            $items = ($fine->details ?? collect())->map(function ($detail) {
                $kondisi = $detail->condition ?? 'Baik';
                return [
                    'title' => $detail->book->title ?? '-',
                    'condition' => $kondisi,
                    'price' => (int) ($detail->book_price_snapshot ?: ($detail->book->price ?? 0)),
                    'percent' => (int) ($detail->damage_percent ?? 0),
                    'condition_fine' => (int) ($detail->condition_fine ?? 0),
                    'late_fine' => (int) ($detail->late_fine ?? 0),
                ];
            })->values();

            $jenis = [];
            if ($lateDays > 0) $jenis[] = 'Keterlambatan';
            if ($items->contains(fn ($i) => strtolower($i['condition']) === 'rusak')) $jenis[] = 'Rusak';
            if ($items->contains(fn ($i) => strtolower($i['condition']) === 'hilang')) $jenis[] = 'Hilang';

            return [
                'id' => $fine->id,
                'code' => 'DEN-' . str_replace('TRX-', '', $fine->transaction_code ?? $fine->id),
                'date' => $fine->return_date ? \Carbon\Carbon::parse($fine->return_date)->translatedFormat('d M Y H:i') : '-',
                'student_name' => $fine->student->name ?? '-',
                'student_class' => $fine->student->class ?? '-',
                'student_nis' => $fine->student->nis ?? '-',
                'student_initial' => strtoupper(substr($fine->student->name ?? 'U', 0, 2)),
                'type' => count($jenis) ? implode(' + ', $jenis) : 'Denda',
                'description' => $fine->fine->keterangan ?? ('Terlambat ' . $lateDays . ' hari (' . $bookCount . ' buku)'),
                'items' => $items,
                'total_fine' => abs($fine->fine->total_fine ?? 0), 
                'status' => ($fine->fine->fine_status ?? '') === 'paid' ? 'paid' : 'unpaid', 
                'payment_method' => $fine->fine->payment_method ?? 'Tunai',
                'payment_date' => $fine->fine->payment_date ? \Carbon\Carbon::parse($fine->fine->payment_date)->translatedFormat('d M Y H:i') : ($fine->fine->updated_at ? \Carbon\Carbon::parse($fine->fine->updated_at)->translatedFormat('d M Y H:i') : '-'),
                'processed_by' => $fine->fine->processed_by ?? (auth()->user()->name ?? 'Administrator'),
                'transfer_reference' => $fine->fine->transfer_reference ?? '-',
                'borrow_date' => \Carbon\Carbon::parse($fine->borrow_date)->translatedFormat('d M Y'),
                'due_date' => \Carbon\Carbon::parse($fine->due_date)->translatedFormat('d M Y'),
                'return_date_formatted' => $fine->return_date ? \Carbon\Carbon::parse($fine->return_date)->translatedFormat('d M Y') : '-',
                'late_days' => $lateDays,
                'late_books_count' => $bookCount
            ];
        })->values();
    @endphp

    <div x-data="finesManagement()">
        <div class="print:hidden space-y-4">
        
        <!-- SUMMARY CARDS -->
        <div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1 -->
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Total Denda</p>
                        <p class="text-[15px] font-medium text-gray-800">Rp {{ number_format($totalDendaKeseluruhan, 0, ',', '.') }}</p>
                        <p class="text-[9px] text-gray-400 mt-0.5">Selama periode ini</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Denda Dibayar</p>
                        <p class="text-[15px] font-medium text-gray-800">Rp {{ number_format($dendaDibayar, 0, ',', '.') }}</p>
                        <p class="text-[9px] text-gray-400 mt-0.5">{{ $persenDibayar }}% dari total denda</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Denda Belum Dibayar</p>
                        <p class="text-[15px] font-medium text-gray-800">Rp {{ number_format($dendaBelumDibayar, 0, ',', '.') }}</p>
                        <p class="text-[9px] text-gray-400 mt-0.5">{{ $persenBelum }}% dari total denda</p>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-lg">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Total Transaksi Denda</p>
                        <p class="text-[15px] font-medium text-gray-800">{{ $fines->total() ?? $fines->count() }}</p>
                        <p class="text-[9px] text-gray-400 mt-0.5">Transaksi tercatat</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER SECTION -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <form action="{{ route('admin.fines.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4">
                <div class="flex-1 w-full">
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1.5">Cari Anggota</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-[11px]"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama anggota atau NIS..." 
                               class="w-full border border-gray-200 rounded-lg text-[11px] py-2 pl-8 pr-3 focus:ring-1 focus:ring-blue-500 bg-gray-50 focus:bg-white outline-none">
                    </div>
                </div>
                <div class="w-full md:w-48">
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1.5">Status Pembayaran</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg text-[11px] py-2 px-3 focus:ring-1 focus:ring-blue-500 bg-gray-50 focus:bg-white outline-none">
                        <option value="">Semua Status</option>
                        <option value="unpaid" @selected(request('status') == 'unpaid')>Belum Dibayar</option>
                        <option value="paid" @selected(request('status') == 'paid')>Sudah Dibayar</option>
                    </select>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium text-[11px] transition-colors shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-filter text-[10px]"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.fines.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-[11px] font-medium transition-colors flex items-center gap-1.5">
                        <i class="fas fa-sync-alt text-[10px]"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- MAIN LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            
            <!-- KOLOM KIRI: TABEL -->
            <div class="lg:col-span-8 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h2 class="text-[13px] font-medium text-gray-800 tracking-tight">Daftar Transaksi Denda</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[11px]">
                        <thead class="bg-gray-50/50 text-gray-500 text-[10px] uppercase tracking-wider font-medium border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-center w-10">No.</th>
                                <th class="px-4 py-3">ID Denda</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Anggota</th>
                                <th class="px-4 py-3">Keterangan</th>
                                <th class="px-4 py-3 text-right">Total Denda</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center w-12">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-gray-700">
                            @forelse($fines as $fine)
                                @php 
                                    $isPaid = ($fine->fine->fine_status == 'paid'); 
                                    $idDenda = 'DEN-' . str_replace('TRX-', '', $fine->transaction_code ?? $fine->id);
                                @endphp
                                <tr @click="selectFine({{ $fine->id }})" 
                                    class="hover:bg-blue-50/40 cursor-pointer transition-colors"
                                    :class="{ 'bg-blue-50/40': selectedFine && selectedFine.id === {{ $fine->id }} }">
                                    <td class="px-4 py-3 text-center text-gray-400 font-medium">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-700">{{ $idDenda }}</td>
                                    <td class="px-4 py-3">
                                        <p>{{ $fine->return_date ? \Carbon\Carbon::parse($fine->return_date)->translatedFormat('d M Y') : '-' }}</p>
                                        <p class="text-[9px] text-gray-400">{{ $fine->return_date ? \Carbon\Carbon::parse($fine->return_date)->translatedFormat('H:i') : '' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">{{ $fine->student->name ?? '-' }}</p>
                                        <p class="text-[9px] text-gray-400">{{ $fine->student->class ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $dueDate = \Carbon\Carbon::parse($fine->due_date)->startOfDay();
                                            $returnDate = $fine->return_date ? \Carbon\Carbon::parse($fine->return_date)->startOfDay() : now()->startOfDay();
                                            $lateDays = $returnDate->isAfter($dueDate) ? $returnDate->diffInDays($dueDate) : 0;
                                        @endphp
                                        <p class="font-medium text-gray-600">{{ $fine->fine->keterangan ?? ('Terlambat ' . $lateDays . ' hari') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800">
                                        <!-- PERBAIKAN: Gunakan $fine, BUKAN $trx -->
                                        Rp. {{ number_format($fine->fine->total_fine ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($isPaid)
                                            <span class="bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded text-[9px] font-medium border border-emerald-100">Dibayar</span>
                                        @else
                                            <span class="bg-red-50 text-red-500 px-2 py-0.5 rounded text-[9px] font-medium border border-red-100">Belum Dibayar</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button class="w-6 h-6 rounded bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white flex items-center justify-center transition-colors mx-auto">
                                            <i class="fas fa-eye text-[10px]"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-[11px] font-medium">Belum ada data denda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(method_exists($fines, 'hasPages') && $fines->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
                        {{ $fines->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>

            <!-- KOLOM KANAN: DETAIL PANEL -->
            <div class="lg:col-span-4 relative">
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-5 sticky top-5 min-h-125">
                    
                    <!-- State if nothing is selected -->
                    <div x-show="!selectedFine" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 bg-white/80 rounded-xl z-10 backdrop-blur-[1px]">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-receipt text-gray-400 text-lg"></i>
                        </div>
                        <p class="text-gray-500 font-medium text-[12px]">Pilih data denda di tabel<br>untuk melihat detail dan proses pembayaran</p>
                    </div>

                    <!-- Panel Content -->
                    <template x-if="selectedFine">
                        <div class="space-y-5 animate-fade-in">
                            <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                                <h3 class="font-medium text-gray-800 text-[13px]">Detail Denda</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium border"
                                      :class="selectedFine.status === 'paid' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-500 border-red-100'" 
                                      x-text="selectedFine.status === 'paid' ? 'Dibayar' : 'Belum Dibayar'">
                                </span>
                            </div>

                            <div class="text-[11px]">
                                <span class="text-gray-400 font-medium uppercase tracking-wider text-[9px] block mb-1">ID Denda</span>
                                <span class="font-medium text-gray-800 text-[13px]" x-text="selectedFine.code"></span>
                            </div>

                            <!-- Profil Info -->
                            <div>
                                <span class="text-gray-400 font-medium uppercase tracking-wider text-[9px] block mb-2">Informasi Anggota</span>
                                <div class="bg-blue-50/40 border border-blue-100/50 rounded-lg p-3 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium text-[12px] shadow-sm shrink-0">
                                        <span x-text="selectedFine.student_initial"></span>
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <p class="font-medium text-gray-800 text-[12px] truncate" x-text="selectedFine.student_name"></p>
                                        <p class="text-[10px] text-gray-500 mt-0.5"><span x-text="selectedFine.student_class"></span> <span class="mx-1">•</span> NIS: <span x-text="selectedFine.student_nis"></span></p>
                                    </div>
                                    <a href="#" class="text-[10px] text-blue-600 font-medium bg-white border border-blue-100 px-2 py-1 rounded shadow-sm hover:bg-blue-50 whitespace-nowrap">Lihat Profil</a>
                                </div>
                            </div>

                            <!-- Detail Rincian -->
                            <div class="space-y-2.5 text-[11px] text-gray-600 border-b border-gray-100 pb-4">
                                <span class="text-gray-400 font-medium uppercase tracking-wider text-[9px] block mb-2">Detail Denda</span>
                                
                                <div class="flex justify-between"><span class="font-medium text-gray-500">Jenis Denda</span><span class="font-medium text-gray-800" x-text="selectedFine.type"></span></div>
                                <div class="flex justify-between"><span class="font-medium text-gray-500">Tanggal Pinjam</span><span class="font-medium text-gray-800" x-text="selectedFine.borrow_date"></span></div>
                                <div class="flex justify-between"><span class="font-medium text-gray-500">Tanggal Kembali Seharusnya</span><span class="font-medium text-gray-800" x-text="selectedFine.due_date"></span></div>
                                <div class="flex justify-between"><span class="font-medium text-gray-500">Tanggal Kembali Aktual</span><span class="font-medium text-gray-800" x-text="selectedFine.return_date_formatted"></span></div>
                                <div class="flex justify-between"><span class="font-medium text-gray-500">Terlambat</span><span class="font-medium text-gray-800" x-text="selectedFine.late_days + ' hari'"></span></div>
                                <p class="text-[10px] text-gray-500 leading-relaxed" x-text="selectedFine.description"></p>
                                <template x-for="item in (selectedFine.items || [])" :key="item.title">
                                    <div class="bg-gray-50 rounded-lg px-2 py-1.5 text-[10px] flex justify-between gap-2">
                                        <span class="text-gray-600 truncate" x-text="item.title + ' (' + item.condition + (item.percent ? ' ' + item.percent + '%' : '') + ')'"></span>
                                        <span class="font-medium text-gray-800 whitespace-nowrap" x-text="formatRupiah((item.late_fine || 0) + (item.condition_fine || 0))"></span>
                                    </div>
                                </template>
                                
                                <div class="flex justify-between items-center pt-3 mt-1 border-t border-gray-50">
                                    <span class="font-medium text-gray-800">Total Denda</span>
                                    <span class="font-medium text-red-600 text-[15px]" x-text="formatRupiah(selectedFine.total_fine)"></span>
                                </div>
                            </div>

                            <!-- Payment Form (Only show if unpaid) -->
                            <template x-if="selectedFine.status === 'unpaid'">
                                <form :action="'{{ route('admin.fines.pay', ['borrowing' => 'REPLACE_ID']) }}'.replace('REPLACE_ID', selectedFine.id)" method="POST" class="mt-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                    @csrf
                                    @method('PATCH')
                                    <p class="text-[10px] font-medium text-gray-800 uppercase tracking-wider mb-3"><i class="fas fa-wallet mr-1"></i> Aksi Pembayaran</p>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-500 mb-1">Jumlah Bayar (Rp)</label>
                                            <input type="text" readonly :value="selectedFine.total_fine" class="w-full border border-gray-200 rounded-lg text-[12px] font-medium text-gray-700 py-2 px-3 bg-gray-100 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-500 mb-1">Metode Pembayaran</label>
                                            <select name="payment_method" x-model="paymentMethod" required class="w-full border border-gray-200 rounded-lg text-[11px] py-2 px-3 bg-white focus:ring-1 focus:ring-blue-500 outline-none">
                                                <option value="Tunai">Tunai</option>
                                                <option value="Transfer">Transfer Bank</option>
                                            </select>
                                        </div>
                                        
                                        <template x-if="paymentMethod === 'Transfer'">
                                            <div class="space-y-3 animate-fade-in">
                                                <div>
                                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Nama Bank</label>
                                                    <input type="text" name="bank_name" required placeholder="Contoh: BCA, Mandiri" class="w-full border border-gray-200 rounded-lg text-[11px] py-2 px-3 focus:ring-1 focus:ring-blue-500 outline-none bg-white">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Nomor Referensi Transfer</label>
                                                    <input type="text" name="transfer_reference" required placeholder="Masukkan nomor referensi" class="w-full border border-gray-200 rounded-lg text-[11px] py-2 px-3 focus:ring-1 focus:ring-blue-500 outline-none bg-white">
                                                </div>
                                            </div>
                                        </template>

                                        <button type="submit" class="w-full mt-2 py-2.5 bg-[#2563EB] text-white rounded-lg text-[11px] font-medium hover:bg-blue-700 transition-all shadow-sm flex items-center justify-center gap-2">
                                            <i class="fas fa-check-circle"></i> Proses Pembayaran
                                        </button>
                                    </div>
                                </form>
                            </template>

                            <!-- Notice if already paid -->
                            <template x-if="selectedFine.status === 'paid'">
                                <div class="mt-4 space-y-3 animate-fade-in">
                                    <div class="bg-emerald-50/50 border border-emerald-100 text-emerald-600 p-3 rounded-lg text-center text-[10px] font-medium">
                                        <i class="fas fa-check-circle mb-1 text-lg block"></i>
                                        Denda ini sudah dibayar lunas.
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 space-y-2 text-[10px]">
                                        <div class="flex justify-between"><span class="text-gray-500">Tanggal Pembayaran</span><span class="font-medium text-gray-800" x-text="selectedFine.payment_date"></span></div>
                                        <div class="flex justify-between"><span class="text-gray-500">Petugas</span><span class="font-medium text-gray-800" x-text="selectedFine.processed_by"></span></div>
                                        <div class="flex justify-between"><span class="text-gray-500">Metode</span><span class="font-medium text-gray-800" x-text="selectedFine.payment_method"></span></div>
                                        <template x-if="selectedFine.payment_method === 'Transfer'">
                                            <div class="flex justify-between"><span class="text-gray-500">No. Referensi</span><span class="font-medium text-gray-800" x-text="selectedFine.transfer_reference"></span></div>
                                        </template>
                                    </div>
                                    <button type="button" @click="window.print()" class="w-full py-2.5 bg-white border border-gray-200 text-[#2563EB] rounded-lg text-[11px] font-medium hover:bg-blue-50 transition-all shadow-sm flex items-center justify-center gap-2">
                                        <i class="fas fa-print"></i> Cetak Struk
                                    </button>
                                </div>
                            </template>

                        </div>
                    </template>
                </div>
            </div>
        </div>
        </div> <!-- End of print:hidden wrapper -->

        <!-- PRINT RECEIPT AREA -->
        <div class="hidden print:block w-[80mm] mx-auto text-black bg-white" id="print-area">
            <template x-if="selectedFine">
                <div class="p-2 font-mono text-[11px]">
                    <div class="text-center border-b border-dashed border-gray-400 pb-2 mb-2">
                        <i class="fas fa-graduation-cap text-[24px] mb-1"></i>
                        <div class="font-medium text-[14px] uppercase mb-1">SMK Budi Mulia</div>
                        <div>Sistem Informasi Perpustakaan</div>
                        <div class="text-[9px] mt-1">Struk Pembayaran Denda</div>
                    </div>
                    
                    <div class="space-y-1 mb-2">
                        <div class="flex justify-between"><span>No Struk:</span><span x-text="selectedFine.code"></span></div>
                        <div class="flex justify-between"><span>ID Denda:</span><span x-text="selectedFine.code"></span></div>
                        <div class="flex justify-between"><span>Tgl:</span><span x-text="selectedFine.payment_date"></span></div>
                        <div class="flex justify-between"><span>Petugas:</span><span x-text="selectedFine.processed_by"></span></div>
                    </div>

                    <div class="border-t border-b border-dashed border-gray-400 py-2 mb-2 space-y-1">
                        <div><span class="font-medium">Siswa:</span> <span x-text="selectedFine.student_name"></span> (<span x-text="selectedFine.student_nis"></span>)</div>
                        <div><span class="font-medium">Jenis:</span> <span x-text="selectedFine.type"></span></div>
                        <div><span class="font-medium">Ket:</span> <span x-text="selectedFine.description"></span></div>
                    </div>

                    <div class="space-y-1 mb-2">
                        <div class="flex justify-between"><span>Metode:</span><span x-text="selectedFine.payment_method"></span></div>
                        <template x-if="selectedFine.payment_method === 'Transfer'">
                            <div class="flex justify-between"><span>Ref:</span><span x-text="selectedFine.transfer_reference"></span></div>
                        </template>
                    </div>

                    <div class="border-t border-gray-400 pt-2 mb-4">
                        <div class="flex justify-between font-medium text-[13px]">
                            <span>TOTAL:</span>
                            <span x-text="formatRupiah(selectedFine.total_fine)"></span>
                        </div>
                    </div>

                    <div class="text-center mt-4 mb-2">
                        <div class="inline-block border-2 border-black px-3 py-1 font-medium tracking-widest uppercase transform -rotate-6">LUNAS</div>
                    </div>
                    <div class="text-center text-[9px] text-gray-600 mt-2">
                        <p>Terima kasih atas pembayaran Anda</p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Script Alpine.js -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('finesManagement', () => ({
                fines: {!! json_encode($alpineFines) !!},
                selectedFine: null,
                paymentMethod: 'Tunai',

                init() {
                    // Opsional: Pilih baris pertama secara otomatis
                    // if (this.fines.length > 0) this.selectedFine = this.fines[0];
                },

                selectFine(id) {
                    this.selectedFine = this.fines.find(f => f.id === id);
                    this.paymentMethod = 'Tunai'; // Reset form
                },

                formatRupiah(angka) {
                    return "Rp " + new Intl.NumberFormat("id-ID").format(angka || 0);
                }
            }))
        })
    </script>

    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        @media print {
            /* Sembunyikan elemen layout standar (Sidebar, Navbar, dll) */
            aside, header, nav, footer, .sidebar, .navbar {
                display: none !important;
            }
            
            /* Sembunyikan semua elemen secara default */
            body * {
                visibility: hidden;
            }
            
            /* Tampilkan HANYA area print dan isinya */
            #print-area, #print-area * {
                visibility: visible;
            }
            
            /* Posisikan area print di pojok kiri atas untuk menghindari ruang kosong / blank pages */
            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
                padding: 0;
                margin: 0;
            }
            
            /* Atur ukuran kertas ke thermal printer 80mm */
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</x-layouts.app>