<x-layouts.app title="Riwayat Transaksi">
    <div x-data="{ 
        showModal: false,
        selectedTrx: {{ json_encode([
            'id' => $selectedTrx->id ?? null,
            'code' => $selectedTrx->transaction_code ?? '',
            'status' => $selectedTrx->status ?? '',
            'student' => [
                'name' => $selectedTrx->student->name ?? '',
                'class' => $selectedTrx->student->class ?? '',
                'major' => $selectedTrx->student->major ?? 'Tidak Diketahui',
                'nis' => $selectedTrx->student->nis ?? '',
                'photo' => $selectedTrx->student->photo ?? ''
            ],
            'date' => $selectedTrx->borrow_date ? $selectedTrx->borrow_date->format('d M Y H:i') : '',
            'borrow_date' => $selectedTrx->borrow_date ? $selectedTrx->borrow_date->format('d M Y') : '-',
            'due_date' => $selectedTrx->due_date ? $selectedTrx->due_date->format('d M Y') : '-',
            'duration' => ($selectedTrx->borrow_date && $selectedTrx->due_date) ? $selectedTrx->borrow_date->diffInDays($selectedTrx->due_date) : 7,
            'days_passed' => $selectedTrx->borrow_date ? $selectedTrx->borrow_date->diffInDays(now()) : 0,
            'user' => $selectedTrx->user->name ?? '',
            'books' => $selectedTrx ? $selectedTrx->details->map(fn($d) => [
                'title' => $d->book->title ?? 'Tidak Diketahui',
                'author' => $d->book->author ?? '-',
                'cover' => $d->book->cover_image ?? '',
                'isbn' => $d->book->isbn ?? '-',
                'category' => $d->book->category->name ?? '-',
                'rak' => $d->book->rak->nama_rak ?? '-'
            ]) : [],
            'fine' => [
                'has_fine' => ($selectedTrx->fine->total_fine ?? 0) > 0,
                'total' => $selectedTrx->fine->total_fine ?? 0,
                'late_days' => ($selectedTrx->status == 'borrowed' && $selectedTrx->due_date && $selectedTrx->due_date->isPast()) ? $selectedTrx->due_date->diffInDays(now()) : 0,
                'rate' => 1000
            ]
        ]) }},
        
        viewDetail(data) {
            this.selectedTrx = data;
        }
    }" class="space-y-6 text-xs text-gray-600 relative">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-sm"><i class="fas fa-exchange-alt"></i></div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-wider">Total Transaksi</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($stats['total']) }}</h3>
                    <p class="text-[9px] text-gray-400">Semua transaksi</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex items-center justify-center text-lg"><i class="fas fa-file-export"></i></div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-wider">Peminjaman</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($stats['borrowing']) }}</h3>
                    <p class="text-[9px] text-gray-400">Sedang dipinjam</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center text-lg"><i class="fas fa-file-import"></i></div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-wider">Pengembalian</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($stats['returned']) }}</h3>
                    <p class="text-[9px] text-gray-400">Telah dikembalikan</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-lg"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-wider">Terlambat</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($stats['overdue']) }}</h3>
                    <p class="text-[9px] text-gray-400">Melewati batas waktu</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-wrap items-end gap-4">
            <form action="{{ route('admin.transactions.history') }}" method="GET" class="flex-1 flex gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama anggota atau kode transaksi..." class="w-full pl-4 pr-10 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="w-48">
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua Status</option>
                        <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Peminjaman</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Pengembalian</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-[11px] text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fas fa-filter text-[11px]"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.transactions.history') }}" class="border border-gray-200 text-gray-500 px-4 py-2 rounded-lg hover:bg-gray-100 transition flex items-center justify-center">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="col-span-1 lg:col-span-8 space-y-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50 flex justify-between items-center text-gray-800 font-medium">
                        <h2 class="text-gray-700 font-medium flex items-center gap-1.5 text-[13px]">Daftar Riwayat Transaksi <span class="ml-0.5 px-1.5 py-0.5 bg-gray-100 rounded-full text-[9px] text-gray-500 font-normal">{{ $transactions->total() }} data</span></h2>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.transactions.history.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="text-[11px] font-medium text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg flex items-center gap-2 hover:bg-gray-50 transition">
                                <i class="fas fa-file-excel text-green-600"></i> Export Excel
                            </a>
                            <a href="{{ route('admin.transactions.history.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="text-[11px] font-medium text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg flex items-center gap-2 hover:bg-gray-50 transition">
                                <i class="fas fa-file-pdf text-red-600"></i> Export PDF
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap text-[11px]">
                            <thead class="bg-gray-50 text-[10px] text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-3 font-medium">No.</th>
                                    <th class="px-4 py-3 font-medium">Kode Transaksi</th>
                                    <th class="px-4 py-3 font-medium">Jenis</th>
                                    <th class="px-4 py-3 font-medium">Tanggal</th>
                                    <th class="px-4 py-3 font-medium">Anggota</th>
                                    <th class="px-4 py-3 font-medium text-center">Buku</th>
                                    <th class="px-4 py-3 font-medium text-center">Status</th>
                                    <th class="px-4 py-3 font-medium text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($transactions as $index => $trx)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-400">{{ $transactions->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-700 uppercase">{{ $trx->transaction_code }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-1 py-1 rounded text-[10px] font-medium border {{ $trx->status == 'returned' ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-green-50 text-green-600 border-green-100' }}">
                                            {{ $trx->status == 'returned' ? 'Pengembalian' : 'Peminjaman' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $trx->borrow_date->format('d M Y') }}<br>
                                        <span class="text-[10px] text-gray-400">{{ $trx->borrow_date->format('H:i') }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-700">
                                        {{ $trx->student->name ?? 'Tidak Diketahui' }}<br>
                                        <span class="text-[10px] text-gray-500 font-normal uppercase">{{ $trx->student->class ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-[10px] text-gray-700">{{ $trx->details_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-medium {{ $trx->status == 'returned' ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-blue-50 text-blue-600 border border-blue-100' }}">
                                            {{ $trx->status == 'returned' ? 'Selesai' : 'Diproses' }}
                                        </span>
                                    </td>
                                    <td class="px-1 py-1 text-center">
                                        <button @click="viewDetail({{ json_encode([
                                            'id' => $trx->id,
                                            'code' => $trx->transaction_code,
                                            'status' => $trx->status,
                                            'student' => [
                                                'name' => $trx->student->name ?? 'Tidak Diketahui',
                                                'class' => $trx->student->class ?? '-',
                                                'major' => $trx->student->major ?? 'Tidak Diketahui',
                                                'nis' => $trx->student->nis ?? '-',
                                                'photo' => $trx->student->photo ?? ''
                                            ],
                                            'date' => $trx->borrow_date->format('d M Y H:i'),
                                            'borrow_date' => $trx->borrow_date ? $trx->borrow_date->format('d M Y') : '-',
                                            'due_date' => $trx->due_date ? $trx->due_date->format('d M Y') : '-',
                                            'duration' => ($trx->borrow_date && $trx->due_date) ? $trx->borrow_date->diffInDays($trx->due_date) : 7,
                                            'days_passed' => $trx->borrow_date ? $trx->borrow_date->diffInDays(now()) : 0,
                                            'user' => $trx->user->name ?? 'Tidak Diketahui',
                                            'books' => $trx->details->map(fn($d) => [
                                                'title' => $d->book->title ?? 'Buku Dihapus',
                                                'author' => $d->book->author ?? '-',
                                                'cover' => $d->book->cover_image ?? '',
                                                'isbn' => $d->book->isbn ?? '-',
                                                'category' => $d->book->category->name ?? '-',
                                                'rak' => $d->book->rak->nama_rak ?? '-',
                                            ]),
                                            'fine' => [
                                                'has_fine' => ($trx->fine->total_fine ?? 0) > 0,
                                                'total' => $trx->fine->total_fine ?? 0,
                                                'late_days' => ($trx->status == 'borrowed' && $trx->due_date && $trx->due_date->isPast()) ? $trx->due_date->diffInDays(now()) : (($trx->status == 'returned' && $trx->return_date && $trx->return_date > $trx->due_date) ? $trx->due_date->diffInDays($trx->return_date) : 0),
                                                'rate' => 1000
                                            ]
                                        ]) }})" 
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition flex items-center justify-center mx-auto shadow-sm">
                                            <i class="fas fa-eye text-[10px]"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-4 transform scale-90 origin-left w-[111%]">
                    {{ $transactions->links() }}
                </div>
            </div>

            <div class="col-span-1 lg:col-span-4">
                <template x-if="selectedTrx">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden sticky top-4">
                        <div class="p-4 border-b border-gray-50 flex justify-between items-center text-gray-800 font-medium">
                            <h2 class="text-sm">Detail Singkat</h2>
                            <span :class="selectedTrx.status === 'returned' ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-green-50 text-green-600 border-green-100'" 
                                class="px-2.5 py-1 text-[10px] font-medium rounded uppercase border"
                                x-text="selectedTrx.status === 'returned' ? 'Pengembalian' : 'Peminjaman'"></span>
                        </div>
                        
                        <div class="p-5 space-y-5">
                            <div>
                                <p class="text-gray-400 text-[10px] uppercase font-medium tracking-wider mb-1">Kode Transaksi</p>
                                <h3 class="text-xs font-medium text-gray-800 uppercase" x-text="selectedTrx.code"></h3>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 text-xl border-2 border-white shadow-sm overflow-hidden shrink-0">
                                    <template x-if="selectedTrx.student.photo">
                                        <img :src="'/storage/' + selectedTrx.student.photo" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!selectedTrx.student.photo">
                                        <i class="fas fa-user"></i>
                                    </template>
                                </div>
                                <div class="overflow-hidden">
                                    <h4 class="font-medium text-gray-800 text-[10px] truncate" x-text="selectedTrx.student.name"></h4>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide truncate" 
                                    x-text="selectedTrx.student.class + ' • NIS: ' + selectedTrx.student.nis"></p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-5">
                                <div>
                                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-wider mb-1">Tanggal</p>
                                    <p class="font-medium text-gray-700 text-[10px]" x-text="selectedTrx.date"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-wider mb-1">Petugas</p>
                                    <p class="font-medium text-gray-700 text-[10px] truncate" x-text="selectedTrx.user"></p>
                                </div>
                            </div>

                            <div class="space-y-3 pt-5 border-t border-gray-100">
                                <p class="text-gray-400 text-[10px] uppercase font-medium tracking-wider">Daftar Buku</p>
                                <template x-for="(book, index) in selectedTrx.books" :key="index">
                                    <div class="flex items-center gap-4 group cursor-pointer bg-white border border-gray-50 p-2 rounded-lg hover:border-blue-100 hover:bg-blue-50/30 transition">
                                        <img :src="book.cover ? '/covers/' + book.cover : '/images/no-cover.png'" class="w-10 h-14 rounded shadow-sm object-cover border border-gray-100 group-hover:scale-105 transition-transform shrink-0">
                                        <div class="overflow-hidden">
                                            <h5 class="font-medium text-gray-700 text-[10px] truncate group-hover:text-blue-600 transition-colors" x-text="book.title"></h5>
                                            <p class="text-[10px] text-gray-500 truncate mt-0.5" x-text="book.author"></p>
                                        </div>
                                        <span class="ml-auto text-gray-300 font-medium text-[10px]" x-text="'#' + (index + 1)"></span>
                                    </div>
                                </template>
                            </div>

                            <button @click="showModal = true" class="w-full bg-blue-50 text-blue-600 py-1.5 rounded-xl font-medium hover:bg-blue-600 hover:text-white transition flex items-center justify-center gap-2 border border-blue-100 shadow-sm mt-2">
                                <i class="fas fa-external-link-alt text-[9px]"></i> Lihat Detail Lengkap
                            </button>
                        </div>
                    </div>
                </template>
                
                <template x-if="!selectedTrx">
                     <div class="bg-gray-50 rounded-xl border border-gray-200 border-dashed h-64 flex flex-col items-center justify-center text-gray-400">
                         <i class="fas fa-hand-pointer text-3xl mb-3 text-gray-300"></i>
                         <p class="text-[10px]">Pilih transaksi untuk melihat detail</p>
                     </div>
                </template>
            </div>
        </div>

        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     @click.away="showModal = false"
                     class="relative flex flex-col transform overflow-hidden rounded-2xl bg-gray-50 text-left shadow-2xl transition-all w-full max-w-5xl max-h-[90vh]">
                    
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex items-start justify-between shrink-0">
                        <template x-if="selectedTrx">
                            <div>
                                <h3 class="text-sm font-medium text-blue-600" id="modal-title">Detail Transaksi Peminjaman</h3>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-gray-500 font-medium text-[10px]" x-text="selectedTrx.code"></span>
                                    <span class="px-3 py-1 rounded-full text-[8px] font-medium border"
                                        :class="{
                                            'bg-green-50 text-green-600 border-green-200': selectedTrx.status === 'borrowed',
                                            'bg-blue-50 text-blue-600 border-blue-200': selectedTrx.status === 'returned',
                                            'bg-red-50 text-red-600 border-red-200': selectedTrx.status === 'late'
                                        }"
                                        x-text="selectedTrx.status === 'borrowed' ? 'DIPINJAM' : (selectedTrx.status === 'returned' ? 'DIKEMBALIKAN' : 'TERLAMBAT')">
                                    </span>
                                </div>
                            </div>
                        </template>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 focus:outline-none bg-gray-100 hover:bg-gray-200 w-8 h-8 rounded-full flex items-center justify-center transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="px-6 py-6 overflow-y-auto flex-1">
                        <template x-if="selectedTrx">
                            <div class="space-y-4">
                                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap items-center gap-5">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center border border-gray-200 shadow-inner overflow-hidden shrink-0">
                                        <template x-if="selectedTrx.student.photo">
                                            <img :src="'/storage/' + selectedTrx.student.photo" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!selectedTrx.student.photo">
                                            <i class="fas fa-user text-xs text-gray-300"></i>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-xs font-medium text-gray-800 mb-2" x-text="selectedTrx.student.name"></h4>
                                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-[10px] text-gray-500">
                                            <div class="flex items-center gap-2"><i class="fas fa-id-card text-blue-500"></i> NIS: <span class="font-medium text-gray-800" x-text="selectedTrx.student.nis"></span></div>
                                            <div class="flex items-center gap-2"><i class="fas fa-door-open text-blue-500"></i> Kelas: <span class="font-medium text-gray-800" x-text="selectedTrx.student.class"></span></div>
                                            <div class="flex items-center gap-2"><i class="fas fa-book text-blue-500"></i> Jurusan: <span class="font-medium text-gray-800" x-text="selectedTrx.student.major"></span></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
                                        <div class="bg-blue-50 text-blue-600 w-9 h-9 rounded-xl flex items-center justify-center text-sm shrink-0"><i class="fas fa-calendar-check"></i></div>
                                        <div>
                                            <div class="text-gray-400 text-[10px] font-medium mb-0.5">Tanggal Pinjam</div>
                                            <div class="font-medium text-gray-800 text-[10px]" x-text="selectedTrx.borrow_date"></div>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
                                        <div class="bg-orange-50 text-orange-500 w-9 h-9 rounded-xl flex items-center justify-center text-sm shrink-0"><i class="fas fa-calendar-times"></i></div>
                                        <div>
                                            <div class="text-gray-400 text-[10px] font-medium mb-0.5">Jatuh Tempo</div>
                                            <div class="font-medium text-gray-800 text-[10px]" x-text="selectedTrx.due_date"></div>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
                                        <div class="bg-teal-50 text-teal-600 w-9 h-9 rounded-xl flex items-center justify-center text-sm shrink-0"><i class="fas fa-clock"></i></div>
                                        <div>
                                            <div class="text-gray-400 text-[10px] font-medium mb-0.5">Lama Peminjaman</div>
                                            <div class="font-medium text-gray-800 text-[10px]" x-text="selectedTrx.duration + ' Hari'"></div>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
                                        <div class="bg-green-50 text-green-600 w-9 h-9 rounded-xl flex items-center justify-center text-sm shrink-0"><i class="fas fa-bookmark"></i></div>
                                        <div>
                                            <div class="text-gray-400 text-[10px] font-medium mb-0.5">Status Terkini</div>
                                            <div class="font-medium text-gray-800 text-[10px]" x-text="selectedTrx.status === 'returned' ? 'Dikembalikan' : 'Dipinjam'"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                    <div class="lg:col-span-7 space-y-6">
                                        <div>
                                            <h5 class="font-medium text-sm text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-book-open text-blue-500"></i> Detail Buku Dipinjam</h5>
                                            <div class="space-y-4">
                                                <template x-for="book in selectedTrx.books">
                                                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-4">
                                                        <img :src="book.cover ? '/covers/' + book.cover : '/images/no-cover.png'" class="rounded-lg shadow-sm object-cover border border-gray-200 w-20 h-25 shrink-0">
                                                        <div class="flex-1">
                                                            <h6 class="font-medium text-xs text-blue-600 mb-2" x-text="book.title"></h6>
                                                            <div class="grid grid-cols-2 gap-y-2 gap-x-4 text-[10px] text-gray-500">
                                                                <div><span class="text-gray-400 block mb-0.5">Penulis</span><span class="font-medium text-gray-800" x-text="book.author"></span></div>
                                                                <div><span class="text-gray-400 block mb-0.5">ISBN</span><span class="font-medium text-gray-800" x-text="book.isbn"></span></div>
                                                                <div><span class="text-gray-400 block mb-0.5">Kategori</span><span class="font-medium text-gray-800" x-text="book.category"></span></div>
                                                                <div><span class="text-gray-400 block mb-0.5">Rak</span><span class="font-medium text-gray-800" x-text="book.rak"></span></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <template x-if="selectedTrx.status !== 'returned'">
                                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                                                <div class="flex justify-between items-end mb-3">
                                                    <div>
                                                        <h6 class="font-medium text-gray-800">Masa Peminjaman</h6>
                                                        <p class="text-xs text-gray-500 mt-1" x-text="selectedTrx.days_passed + ' Hari Berjalan dari ' + selectedTrx.duration + ' Hari'"></p>
                                                    </div>
                                                    <h5 class="font-medium text-xs" :class="selectedTrx.days_passed >= selectedTrx.duration ? 'text-red-500' : 'text-blue-600'"
                                                        x-text="Math.min(Math.round((selectedTrx.days_passed / selectedTrx.duration) * 100), 100) + '%'"></h5>
                                                </div>
                                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                    <div class="h-1.5 rounded-full transition-all duration-500" 
                                                         :class="{
                                                             'bg-red-500': selectedTrx.days_passed >= selectedTrx.duration,
                                                             'bg-orange-400': (selectedTrx.duration - selectedTrx.days_passed <= 2) && selectedTrx.days_passed < selectedTrx.duration,
                                                             'bg-blue-500': (selectedTrx.duration - selectedTrx.days_passed > 2)
                                                         }"
                                                         :style="`width: ${Math.min((selectedTrx.days_passed / selectedTrx.duration) * 100, 100)}%`">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="lg:col-span-5 space-y-6">
                                        <div>
                                            <h5 class="font-medium text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-stream text-blue-500"></i> Timeline Aktivitas</h5>
                                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                                                <div class="relative border-l-2 border-gray-200 ml-3 space-y-6">
                                                    <div class="relative pl-6">
                                                        <div class="absolute -left-2 top-1 w-4 h-4 rounded-full bg-green-500 border-2 border-white shadow"></div>
                                                        <h6 class="font-medium text-gray-800 text-xs">Pengajuan Peminjaman</h6>
                                                        <p class="text-[8px] text-gray-500 mt-1" x-text="selectedTrx.date"></p>
                                                    </div>
                                                    <div class="relative pl-6">
                                                        <div class="absolute -left-2 top-1 w-4 h-4 rounded-full bg-green-500 border-2 border-white shadow"></div>
                                                        <h6 class="font-medium text-gray-800 text-xs">Disetujui Petugas</h6>
                                                        <p class="text-[8px] text-gray-500 mt-1" x-text="selectedTrx.date + ' oleh ' + selectedTrx.user"></p>
                                                    </div>
                                                    <div class="relative pl-6">
                                                        <div class="absolute -left-2 top-1 w-4 h-4 rounded-full bg-green-500 border-2 border-white shadow"></div>
                                                        <h6 class="font-medium text-gray-800 text-xs">Buku Dipinjam</h6>
                                                        <p class="text-[8px] text-gray-500 mt-1" x-text="selectedTrx.date"></p>
                                                    </div>
                                                    <div class="relative pl-6">
                                                        <div class="absolute -left-2 top-1 w-4 h-4 rounded-full border-2 border-white shadow" :class="selectedTrx.status === 'returned' ? 'bg-green-500' : 'bg-orange-400'"></div>
                                                        <h6 class="font-medium text-xs" :class="selectedTrx.status === 'returned' ? 'text-gray-800' : 'text-gray-500'" x-text="selectedTrx.status === 'returned' ? 'Telah Dikembalikan' : 'Menunggu Pengembalian'"></h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <h5 class="font-medium text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-wallet text-blue-500"></i> Informasi Denda</h5>
                                            
                                            <template x-if="!selectedTrx.fine.has_fine">
                                                <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-green-700">
                                                    <h6 class="font-medium flex items-center gap-2 mb-1"><i class="fas fa-check-circle text-xs"></i> Tidak Ada Denda</h6>
                                                    <p class="text-[10px] opacity-80 mt-1">Buku dikembalikan atau masih dalam masa peminjaman.</p>
                                                </div>
                                            </template>
                                            
                                            <template x-if="selectedTrx.fine.has_fine">
                                                <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-red-700">
                                                    <h6 class="font-medium flex items-center gap-2 mb-4 text-base"><i class="fas fa-exclamation-triangle"></i> Peringatan Denda!</h6>
                                                    <div class="flex justify-between items-center text-[8px] mb-2">
                                                        <span>Terlambat:</span>
                                                        <span class="font-medium" x-text="selectedTrx.fine.late_days + ' Hari'"></span>
                                                    </div>
                                                    <div class="flex justify-between items-center text-[8px] mb-3">
                                                        <span>Tarif Denda:</span>
                                                        <span class="font-medium">Rp1.000/Hari</span>
                                                    </div>
                                                    <div class="border-t border-red-200 pt-3 flex justify-between items-center mt-2">
                                                        <span class="font-medium">Total Denda:</span>
                                                        <span class="font-medium text-xl" x-text="'Rp' + selectedTrx.fine.total.toLocaleString('id-ID')"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="bg-white px-6 py-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4 rounded-b-2xl shrink-0">
                        <div class="flex gap-3">
                            <button @click="showModal = false" type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-xs hover:bg-gray-200 transition">
                                Tutup
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-layouts.app>