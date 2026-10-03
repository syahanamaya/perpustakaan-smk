<x-layouts.app title="Transaksi Pengembalian">
    <div class="space-y-4" x-data="{ showDetail: true }">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-lg">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Total Transaksi</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($total_peminjaman ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400 font-light">Semua transaksi</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center text-lg">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Peminjaman Aktif</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($peminjaman_aktif ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-indigo-600 font-light">{{ $persen_aktif ?? '0' }}% dari total transaksi</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex items-center justify-center text-lg">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Pengembalian</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($total_pengembalian ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-green-600 font-light">{{ $persen_kembali ?? '0' }}% dari total transaksi</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-lg">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Terlambat</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($total_terlambat ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-red-600 font-light">{{ $persen_terlambat ?? '0' }}% dari total transaksi</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                
                <div class="lg:col-span-5" x-data="{ searchQuery: '{{ request('search', '') }}' }">
                    @if(session('success'))
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-lg mb-4 text-[12px] font-medium shadow-sm">
                            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-[12px] font-medium shadow-sm">
                            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.returns.index') }}" method="GET">
                        <label class="text-[11px] font-light text-gray-700 block mb-1.5">Cari Anggota</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="search" x-model="searchQuery" placeholder="Cari berdasarkan nama atau NIS..." class="w-full text-gray-700 border-gray-300 rounded-lg text-[11px] py-1.5 px-3 pr-8 focus:ring-blue-500 bg-white shadow-sm border transition-all">
                                <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''" class="absolute right-2.5 top-0.5 text-gray-400 hover:text-gray-600" x-cloak>
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            </div>
                            <button type="submit" class="bg-[#2563EB] hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg font-medium text-[12px] transition-colors flex items-center gap-1.5 shadow-sm">
                                <i class="fas fa-search text-[11px]"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>

                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @if(isset($student) && $student)
                        <div class="bg-gray-50 border border-gray-100 rounded-lg p-2.5 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gray-200 overflow-hidden shrink-0 flex items-center justify-center text-gray-500 font-light text-xs border border-gray-300">
                                {{ strtoupper(substr($student->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-light text-gray-800 text-[12px] leading-tight">{{ $student->name }}</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">{{ $student->nis }} <span class="mx-1">•</span> {{ $student->class ?? 'Kelas' }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-white border border-gray-100 shadow-sm rounded-lg p-2.5 flex items-center gap-3">
                            <div class="bg-green-100 text-green-600 w-8 h-8 rounded flex items-center justify-center shrink-0">
                                <i class="fas fa-book text-[11px]"></i>
                            </div>
                            <div>
                                <p class="text-[9px] text-gray-500 font-light uppercase tracking-wider">Total Peminjaman Aktif</p>
                                <p class="font-medium text-gray-900 text-[13px] leading-tight">{{ $activeBorrowsCount ?? 0 }} buku</p>
                            </div>
                        </div>

                        <div class="bg-white border border-red-50 shadow-sm rounded-lg p-2.5 flex items-center gap-3">
                            <div class="bg-red-100 text-red-600 w-8 h-8 rounded flex items-center justify-center shrink-0">
                                <i class="fas fa-exclamation-circle text-[11px]"></i>
                            </div>
                            <div>
                                <p class="text-[9px] text-gray-500 font-light uppercase tracking-wider">Total Terlambat</p>
                                <p class="font-medium text-gray-900 text-[13px] leading-tight">{{ $lateBorrowsCount ?? 0 }} buku</p>
                            </div>
                        </div>
                    @elseif(request('search'))
                        <div class="sm:col-span-3 bg-red-50 text-red-600 rounded-lg p-3 flex items-center justify-center text-[12px] font-light border border-red-100">
                            <i class="fas fa-user-slash mr-2"></i> Anggota tidak ditemukan.
                        </div>
                    @else
                        <div class="sm:col-span-3 bg-blue-50 text-blue-500 rounded-lg p-3 flex items-center justify-center text-[12px] font-light border border-blue-100">
                            <i class="fas fa-search mr-2"></i> Silakan cari anggota terlebih dahulu.
                        </div>
                    @endif
                </div>
            </div>
        </div>

       @php
            $alpineBooks = [];
            if(isset($student) && isset($activeTransactions) && $activeTransactions->count() > 0) {
                foreach($activeTransactions as $trx) {
                    foreach($trx->details as $detail) {
                        $tglJatuhTempo = strtotime(\Carbon\Carbon::parse($trx->due_date)->format('Y-m-d'));
                        $tglHariIni = strtotime(now()->format('Y-m-d'));
                        $selisih = ($tglHariIni - $tglJatuhTempo) / (60 * 60 * 24);
                        
                        $lateDays = $selisih > 0 ? intval($selisih) : 0;
                        
                        // PERBAIKAN: Paksa tarif menjadi 1000 jika dari controller kosong atau 0
                        $tarifTerlambat = !empty($dendaTerlambat) ? $dendaTerlambat : 1000;
                        $fine = $lateDays * $tarifTerlambat;
                        
                        $words = explode(' ', $detail->book->title ?? 'Buku');
                        $initials = strtoupper(substr($words[0] ?? 'B', 0, 1) . substr($words[1] ?? '', 0, 1));

                        $alpineBooks[] = [
                            'detail_id' => $detail->id,
                            'title' => $detail->book->title ?? 'Buku Tidak Diketahui',
                            'isbn' => $detail->book->isbn ?? '-',
                            'initials' => $initials ?: 'BK',
                            'harga_buku'  => (int) ($detail->book->price ?? $detail->book->harga ?? $detail->book->harga_buku ?? 0),
                            'borrow_date' => \Carbon\Carbon::parse($trx->borrow_date)->format('d M Y'),
                            'due_date' => \Carbon\Carbon::parse($trx->due_date)->format('d M Y'),
                            'is_late' => $lateDays > 0,
                            'late_days' => $lateDays,
                            'fine' => $fine,
                            'condition' => 'Baik',
                            'damage_level' => 'sedang',
                            'selected' => true
                        ];
                    }
                }
            }
        @endphp

        @if(isset($student) && count($alpineBooks) > 0)
            <form action="{{ route('admin.returns.process') }}" method="POST" 
                x-data="returnForm" 
                class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start w-full">
                @csrf
                
                <div class="xl:col-span-3">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="p-4 bg-blue-50/50 border-b border-blue-50 flex items-center gap-3">
                            <i class="fas fa-info-circle text-blue-500 text-[14px]"></i>
                            <h3 class="text-[12px] text-blue-600 font-medium">Pilih satu atau lebih buku yang akan dikembalikan.</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-212.5">
                                <thead>
                                    <tr class="border-b border-gray-50 bg-gray-50/30">
                                        <th class="px-5 py-3 w-10">
                                            <input type="checkbox" id="checkAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        </th>
                                        <th class="px-2 py-3 text-[10px] font-medium text-gray-400 uppercase tracking-wider">No. Buku</th>
                                        <th class="px-4 py-3 text-[10px] font-medium text-gray-400 uppercase tracking-wider">Judul Buku</th>
                                        <th class="px-4 py-3 text-[10px] font-medium text-gray-400 uppercase tracking-wider">Tgl Pinjam</th>
                                        <th class="px-4 py-3 text-[10px] font-medium text-gray-400 uppercase tracking-wider">Jatuh Tempo</th>
                                        <th class="px-4 py-3 text-[10px] font-medium text-gray-400 uppercase tracking-wider text-center">Kondisi</th>
                                        <th class="px-4 py-3 text-[10px] font-medium text-gray-400 uppercase tracking-wider text-right">Terlambat</th>
                                        <th class="px-5 py-3 text-[10px] font-medium text-gray-400 uppercase tracking-wider text-right">Denda</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @php $idx = 0; @endphp
                                    @foreach($activeTransactions as $transaction)
                                        @foreach($transaction->details as $detail)
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-5 py-4">
                                                <input type="checkbox" x-model="books[{{ $idx }}].selected" @change="updateSelection" name="returns[{{ $detail->id }}][detail_id]" value="{{ $detail->id }}" class="checkItem rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                                <input type="hidden" name="returns[{{ $detail->id }}][late_days]" :value="books[{{ $idx }}].late_days" :disabled="!books[{{ $idx }}].selected">
                                                <input type="hidden" name="returns[{{ $detail->id }}][fine]" :value="calculateItemFine({{ $idx }})" :disabled="!books[{{ $idx }}].selected">
                                            </td>
                                            <td class="px-2 py-4 text-[11px] text-gray-400 font-medium">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                                            <td class="px-4 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-10 rounded bg-blue-50 flex items-center justify-center text-blue-500 font-medium text-[10px] shrink-0">
                                                        {{ strtoupper(substr($detail->book->title, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="text-[12px] font-medium text-gray-800 leading-tight">{{ $detail->book->title }}</div>
                                                        <div class="text-[10px] text-gray-400 mt-0.5">ISBN: {{ $detail->book->isbn ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-[11px] text-gray-600">{{ $transaction->borrow_date->format('d M Y') }}</td>
                                            
                                            <td class="px-4 py-4 text-[11px] font-medium text-red-500" x-text="books[{{ $idx }}].due_date"></td>
                                            
                                            <td class="px-4 py-4 text-center">
                                                <div class="flex flex-col items-center gap-1">
                                                    <select x-model="books[{{ $idx }}].condition" 
                                                            :disabled="!books[{{ $idx }}].selected" 
                                                            name="returns[{{ $detail->id }}][condition]" 
                                                            class="text-[10px] border border-gray-200 rounded px-2 py-1 outline-none bg-white text-gray-600 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400">
                                                        <option value="Baik">Baik</option>
                                                        <option value="Rusak">Rusak</option>
                                                        <option value="Hilang">Hilang</option>
                                                    </select>
                                                    <select x-show="books[{{ $idx }}].condition === 'Rusak'"
                                                            x-model="books[{{ $idx }}].damage_level"
                                                            :disabled="!books[{{ $idx }}].selected"
                                                            name="returns[{{ $detail->id }}][damage_level]"
                                                            class="text-[10px] border border-amber-200 rounded px-2 py-1 outline-none bg-amber-50 text-amber-700">
                                                        <option value="ringan">Ringan {{ $persenRusakRingan ?? 25 }}%</option>
                                                        <option value="sedang">Sedang {{ $persenRusakSedang ?? 50 }}%</option>
                                                        <option value="berat">Berat {{ $persenRusakBerat ?? 75 }}%</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <template x-if="books[{{ $idx }}].is_late">
                                                    <span class="text-[11px] font-medium text-red-500 bg-red-50 px-2 py-0.5 rounded" x-text="'+' + books[{{ $idx }}].late_days + ' hari'"></span>
                                                </template>
                                                <template x-if="!books[{{ $idx }}].is_late">
                                                    <span class="text-[11px] font-medium text-green-500 bg-green-50 px-2 py-0.5 rounded">0 hari</span>
                                                </template>
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <div class="flex flex-col items-end gap-1">
                                                    <span class="text-[12px] font-normal text-gray-800" x-text="formatRupiah(calculateItemFine({{ $idx }}))"></span>
                                                    <template x-if="books[{{ $idx }}].condition === 'Hilang'">
                                                        <span class="text-[9px] font-medium text-amber-700 bg-amber-100 border border-amber-200 px-2 py-0.5 rounded">100% harga buku</span>
                                                    </template>
                                                    <template x-if="books[{{ $idx }}].condition === 'Rusak'">
                                                        <span class="text-[9px] font-medium text-orange-700 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded" x-text="damagePercent(books[{{ $idx }}].damage_level) + '% harga buku'"></span>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                        @php $idx++; @endphp
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const checkAll = document.getElementById('checkAll');
                                const checkItems = document.querySelectorAll('.checkItem');

                                checkAll.addEventListener('change', function() {
                                    checkItems.forEach(function(item) {
                                        item.checked = checkAll.checked;
                                        // Pemicu event manual agar Alpine mengetahui perubahan
                                        item.dispatchEvent(new Event('change'));
                                    });
                                });

                                checkItems.forEach(function(item) {
                                    item.addEventListener('change', function() {
                                        const totalItems = checkItems.length;
                                        const totalChecked = document.querySelectorAll('.checkItem:checked').length;
                                        if (totalChecked === totalItems) {
                                            checkAll.checked = true;
                                        } else {
                                            checkAll.checked = false;
                                        }
                                    });
                                });
                            });
                            </script>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-1">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sticky top-5">
                        <div x-show="selectedCount === 0" class="absolute inset-0 bg-white/60 z-10 backdrop-blur-[1px] rounded-xl flex items-center justify-center">
                            <p class="bg-gray-800 text-white px-3 py-1.5 rounded-md text-[10px] font-medium">Pilih minimal 1 buku</p>
                        </div>

                        <h2 class="text-blue-600 font-medium flex items-center gap-2 mb-5 text-[13px]">
                            <i class="fas fa-clipboard-check"></i>Ringkasan Pengembalian
                        </h2>
                        
                        <div class="space-y-3 text-[11px] text-gray-500">
                            <div class="flex justify-between"><span>Total Buku</span><span class="font-medium text-gray-800"><span x-text="selectedCount"></span> buku</span></div>
                            <div class="flex justify-between border-b border-gray-50 pb-3"><span>Total Terlambat</span><span class="font-medium text-gray-800"><span x-text="lateCount"></span> buku</span></div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-gray-800 font-medium">Total Denda</span>
                                <span class="font-medium text-red-600 text-[16px]" x-text="formatRupiah(totalFine)"></span>
                                <input type="hidden" name="total_fine" :value="totalFine">
                            </div>

                            <div x-show="totalFine > 0" class="bg-gray-50 border border-gray-100 rounded-lg p-3 space-y-2">
                                <p class="text-[10px] font-medium text-gray-600 uppercase tracking-wide">Rincian Denda</p>

                                <div x-show="totalLateFine > 0" class="flex justify-between gap-2">
                                    <span class="text-red-600">Denda keterlambatan</span>
                                    <span class="font-medium text-gray-800 text-right" x-text="formatRupiah(totalLateFine)"></span>
                                </div>
                                <div x-show="totalDamageFine > 0" class="flex justify-between gap-2">
                                    <span class="text-orange-600">Denda rusak (<span x-text="damageCount"></span> buku)</span>
                                    <span class="font-medium text-gray-800 text-right" x-text="formatRupiah(totalDamageFine)"></span>
                                </div>
                                <div x-show="totalMissingFine > 0" class="flex justify-between gap-2">
                                    <span class="text-amber-700">Denda hilang (<span x-text="missingCount"></span> buku)</span>
                                    <span class="font-medium text-gray-800 text-right" x-text="formatRupiah(totalMissingFine)"></span>
                                </div>

                                <div x-show="booksWithFineBreakdown.length > 0" class="pt-2 border-t border-gray-200 space-y-2">
                                    <p class="text-[9px] text-gray-500">Detail gabungan per buku:</p>
                                    <template x-for="item in booksWithFineBreakdown" :key="item.index">
                                        <div class="text-[10px] leading-relaxed">
                                            <p class="font-medium text-gray-700" x-text="item.book.title"></p>
                                            <template x-for="(part, partIdx) in item.parts" :key="partIdx">
                                                <p class="pl-2" :class="{
                                                    'text-red-600': part.type === 'terlambat',
                                                    'text-orange-600': part.type === 'rusak',
                                                    'text-amber-700': part.type === 'hilang'
                                                }">
                                                    <span x-text="'• ' + part.label + ': ' + formatRupiah(part.amount)"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="text-[10px] font-medium text-gray-400 uppercase tracking-widest block mb-1.5">Catatan (Opsional)</label>
                            <textarea name="notes" rows="2" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-lg text-[11px] outline-none focus:border-blue-300 resize-none" placeholder="Contoh: Buku robek halaman 5..."></textarea>
                        </div>

                        <button type="submit" :disabled="selectedCount === 0" class="w-full mt-6 py-3 bg-emerald-500 text-white rounded-xl text-[12px] font-medium hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-50">
                            <i class="fas fa-check-circle mr-2"></i> Proses Pengembalian
                        </button>
                    </div>
                </div>

            </form>

        @elseif(isset($student) && count($alpineBooks) == 0)
            <div class="bg-white rounded-xl border border-gray-200 p-8 flex flex-col items-center justify-center text-gray-400 shadow-sm mt-4">
                <i class="fas fa-box-open text-3xl mb-2 text-gray-300"></i>
                <p class="font-medium text-gray-500 text-[13px]">Tidak Ada Pinjaman Aktif</p>
                <p class="text-[11px]">Anggota ini sudah mengembalikan semua bukunya.</p>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm mt-2" 
             x-data="{ showHistoryModal: false, detailTrx: {}, openHistoryModal(trx) { this.detailTrx = trx; this.showHistoryModal = true; }, formatRupiah(angka) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka || 0); }, formatDate(dateString) { if(!dateString) return '-'; return new Date(dateString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); } }">
             
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-gray-700 font-medium flex items-center gap-1.5 text-[13px]">
                    <i class="fas fa-history text-gray-400 text-sm"></i> Riwayat Pengembalian Terakhir
                </h2>                
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[11px]">
                    <thead class="text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="pb-2 w-8 font-normal text-center">No.</th>
                            <th class="pb-2 font-normal">ID Pengembalian</th>
                            <th class="pb-2 font-normal">Anggota</th>
                            <th class="pb-2 font-normal text-center">Tanggal</th>
                            <th class="pb-2 font-normal text-center">Total Buku</th>
                            <th class="pb-2 font-normal text-center">Total Denda</th>
                            <th class="pb-2 font-normal text-center">Petugas</th>
                            <th class="pb-2 font-normal text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse($historyTransactions as $history)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-2.5 text-center text-gray-400">{{ ($historyTransactions->currentPage() - 1) * $historyTransactions->perPage() + $loop->iteration }}.</td>
                                <td class="py-2.5 font-medium text-gray-800">{{ $history->transaction_code }}</td>
                                <td class="py-2.5 font-medium text-gray-800">{{ $history->student->name ?? '-' }}</td>
                                <td class="py-2.5 text-center font-medium">{{ $history->return_date ? \Carbon\Carbon::parse($history->return_date)->translatedFormat('d M Y H:i') : '-' }}</td>
                                <td class="py-2.5 text-center font-medium">{{ $history->details->count() }} buku</td>
                                <td class="py-2.5 text-center font-medium">Rp {{ number_format($history->total_fine, 0, ',', '.') }}</td>
                                <td class="py-2.5 text-center text-gray-500 font-medium">{{ $history->user->name ?? 'Admin' }}</td>
                                <td class="py-2.5 text-center">
                                    <button type="button" @click='openHistoryModal(@json($history))' class="w-6 h-6 rounded bg-blue-50 border border-blue-100 text-blue-500 hover:bg-blue-600 hover:text-white flex items-center justify-center mx-auto transition-colors" title="Lihat Detail">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-8 text-center text-gray-400 italic bg-gray-50/30 font-medium">Belum ada riwayat pengembalian buku.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historyTransactions->hasPages())
                <div class="pt-3 mt-3 border-t border-gray-100">
                    {{ $historyTransactions->links('pagination::tailwind') }}
                </div>
            @endif

            <div x-show="showHistoryModal" class="fixed inset-0 z-70 overflow-y-auto" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-black/50 transition-opacity" @click="showHistoryModal = false"></div>
                    <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full relative z-10">
                        <div class="px-5 py-3.5 border-b border-gray-100 flex justify-between items-center bg-white">
                            <h3 class="text-[14px] font-medium text-gray-800">Detail Pengembalian</h3>
                            <button type="button" @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-light">&times;</button>
                        </div>
                        <div class="p-5 space-y-5 bg-gray-50/30">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                                <div><p class="text-[9px] uppercase font-medium text-gray-400">ID Pengembalian</p><p class="font-medium text-gray-900 text-[11px]" x-text="detailTrx.transaction_code"></p></div>
                                <div><p class="text-[9px] uppercase font-medium text-gray-400">Anggota</p><p class="font-medium text-gray-900 text-[11px]" x-text="detailTrx.student?.name"></p></div>
                                <div><p class="text-[9px] uppercase font-medium text-gray-400">Dikembalikan</p><p class="font-medium text-gray-900 text-[11px]" x-text="formatDate(detailTrx.return_date)"></p></div>
                                <div><p class="text-[9px] uppercase font-medium text-gray-400">Total Denda</p><p class="font-medium text-red-600 text-[11px]" x-text="formatRupiah(detailTrx.total_fine)"></p></div>
                            </div>
                            <div>
                                <p class="text-[11px] font-medium text-gray-700 mb-1.5">Buku yang Dikembalikan</p>
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                    <table class="w-full text-left text-[11px]">
                                        <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-100">
                                            <tr><th class="px-3 py-2 w-8 text-center">No</th><th class="px-3 py-2">Judul Buku</th></tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="(detail, i) in detailTrx.details" :key="detail.id">
                                                <tr class="hover:bg-gray-50"><td class="px-3 py-2 text-center text-gray-400 font-medium" x-text="i + 1"></td><td class="px-3 py-2 font-medium text-gray-800" x-text="detail.book?.title || 'Buku Tidak Diketahui'"></td></tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="px-5 py-3 bg-white border-t border-gray-100 text-right">
                            <button type="button" @click="showHistoryModal = false" class="px-5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[11px] font-medium transition-colors">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('returnForm', () => ({
            books: {!! json_encode($alpineBooks ?? []) !!},
            
            // PERBAIKAN: Gunakan fungsi Number() dan operator || agar dijamin menjadi angka
            // Jika kosong atau 0, otomatis akan diubah menjadi 1000
            dendaTerlambat: Number('{{ $dendaTerlambat ?? 1000 }}') || 1000,
            persenRingan: Number('{{ $persenRusakRingan ?? 25 }}') || 25,
            persenSedang: Number('{{ $persenRusakSedang ?? 50 }}') || 50,
            persenBerat: Number('{{ $persenRusakBerat ?? 75 }}') || 75,
            
            selectAll: true,
            
            toggleAll() { 
                this.books.forEach(b => b.selected = this.selectAll); 
            },
            updateSelection() { 
                this.selectAll = this.books.every(b => b.selected); 
            },
            get selectedCount() { 
                return this.books.filter(b => b.selected).length; 
            },
            get lateCount() { 
                return this.books.filter(b => b.selected && b.is_late).length; 
            },
            damagePercent(level) {
                if (level === 'ringan') return this.persenRingan;
                if (level === 'berat') return this.persenBerat;
                return this.persenSedang;
            },
            
            calculateItemLateFine(index) {
                let b = this.books[index];
                if (!b || !b.selected) return 0;
                return b.is_late ? (b.late_days * this.dendaTerlambat) : 0;
            },
            calculateItemConditionFine(index) {
                let b = this.books[index];
                if (!b || !b.selected) return 0;
                let harga = Number(b.harga_buku) || 0;
                if (b.condition === 'Rusak') {
                    return Math.round(harga * this.damagePercent(b.damage_level) / 100);
                }
                if (b.condition === 'Hilang') {
                    return harga;
                }
                return 0;
            },
            itemFineBreakdown(index) {
                let b = this.books[index];
                if (!b || !b.selected) return [];
                let parts = [];
                let lateFine = this.calculateItemLateFine(index);
                if (lateFine > 0) {
                    parts.push({
                        type: 'terlambat',
                        label: 'Keterlambatan ' + b.late_days + ' hari',
                        amount: lateFine,
                    });
                }
                if (b.condition === 'Rusak') {
                    let amount = this.calculateItemConditionFine(index);
                    if (amount > 0) {
                        parts.push({
                            type: 'rusak',
                            label: 'Rusak ' + b.damage_level + ' (' + this.damagePercent(b.damage_level) + '% harga buku)',
                            amount: amount,
                        });
                    }
                }
                if (b.condition === 'Hilang') {
                    let amount = this.calculateItemConditionFine(index);
                    if (amount > 0) {
                        parts.push({
                            type: 'hilang',
                            label: 'Hilang (100% harga buku)',
                            amount: amount,
                        });
                    }
                }
                return parts;
            },
            calculateItemFine(index) {
                return this.calculateItemLateFine(index) + this.calculateItemConditionFine(index);
            },
            
            get totalLateFine() {
                return this.books.reduce((sum, b, index) => sum + this.calculateItemLateFine(index), 0);
            },
            get totalDamageFine() {
                return this.books.reduce((sum, b, index) => {
                    if (!b.selected || b.condition !== 'Rusak') return sum;
                    return sum + this.calculateItemConditionFine(index);
                }, 0);
            },
            get totalMissingFine() {
                return this.books.reduce((sum, b, index) => {
                    if (!b.selected || b.condition !== 'Hilang') return sum;
                    return sum + this.calculateItemConditionFine(index);
                }, 0);
            },
            get damageCount() {
                return this.books.filter(b => b.selected && b.condition === 'Rusak').length;
            },
            get missingCount() {
                return this.books.filter(b => b.selected && b.condition === 'Hilang').length;
            },
            get booksWithFineBreakdown() {
                return this.books
                    .map((b, index) => ({ book: b, index, parts: this.itemFineBreakdown(index) }))
                    .filter(item => item.parts.length > 1);
            },
            get totalFine() { 
                return this.books.filter(b => b.selected).reduce((sum, b) => {
                    let index = this.books.indexOf(b);
                    return sum + this.calculateItemFine(index);
                }, 0); 
            },
            
            formatRupiah(angka) { 
                return "Rp " + new Intl.NumberFormat("id-ID").format(angka); 
            }
        }))
    })
</script>
</x-layouts.app>