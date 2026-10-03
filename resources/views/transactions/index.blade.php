<x-layouts.app title="Daftar Peminjaman">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <style>
        /* Penyesuaian tema Tom Select dengan Tailwind */
        .ts-control { border-color: #e5e7eb; border-radius: 0.375rem; font-size: 11px; padding: 0.375rem 0.5rem; background-color: #f9fafb; box-shadow: none; }
        .ts-control > input { font-size: 11px; }
        .ts-wrapper.single .ts-control::after { border-color: #9ca3af transparent transparent transparent; }
        .ts-dropdown { font-size: 11px; border-radius: 0.375rem; border-color: #e5e7eb; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .ts-dropdown .option { padding: 0.5rem; }
    </style>

    <script>
        function transactionPage() {
            return {
                openModal: false,
                showCreateModal: {{ ($errors->any() || session('error')) ? 'true' : 'false' }},
                showEditModal: false,
                selectedTrx: {},
                editForm: { id: '', borrow_date: '', due_date: '', notes: '', student_name: '', transaction_code: '', loan_days: 7 },
                classLoanRules: @json($classLoanRules ?? []),
                openEditModal(trx) {
                    this.editForm.id = trx.id;
                    this.editForm.transaction_code = trx.transaction_code;
                    this.editForm.student_name = trx.student ? trx.student.name : '-';
                    this.editForm.borrow_date = trx.borrow_date ? trx.borrow_date.substring(0, 10) : '';
                    this.editForm.due_date = trx.due_date ? trx.due_date.substring(0, 10) : '';
                    this.editForm.notes = trx.notes || '';
                    const details = trx.details || [];
                    const days = [];
                    for (let i = 0; i < details.length; i++) {
                        const cat = details[i].book && details[i].book.category;
                        const n = Number(cat ? cat.loan_days : 7);
                        if (n > 0) days.push(n);
                    }
                    this.editForm.loan_days = days.length ? Math.max.apply(null, days) : 7;
                    this.showEditModal = true;
                },
                addDays(dateStr, days) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    d.setDate(d.getDate() + Number(days || 0));
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return d.getFullYear() + '-' + m + '-' + day;
                },
                maxDueFrom(dateStr) {
                    return this.addDays(dateStr, this.editForm.loan_days || 7);
                }
            };
        }

        function borrowForm() {
            return {
                selectedBooks: [],
                tempBookId: '',
                sisaPaket: 4,
                sisaBebas: 2,
                quotaPaket: 4,
                quotaBebas: 2,
                aktifPaket: 0,
                aktifBebas: 0,
                loanDays: 7,
                classLoanRules: @json($classLoanRules ?? []),
                today() {
                    const d = new Date();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return d.getFullYear() + '-' + m + '-' + day;
                },
                addDays(dateStr, days) {
                    const d = new Date(dateStr);
                    d.setDate(d.getDate() + Number(days || 0));
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return d.getFullYear() + '-' + m + '-' + day;
                },
                applyDuration() {
                    const due = document.getElementById('due_date_input');
                    const hint = document.getElementById('due_date_hint');
                    if (!due) return;
                    const min = this.today();
                    due.min = min;
                    if (!this.selectedBooks.length) {
                        due.value = '';
                        hint.textContent = 'Jatuh tempo dihitung otomatis dari kategori buku yang dipilih.';
                        return;
                    }
                    const values = this.selectedBooks.map(function (b) { return Number(b.loan_days) || 7; });
                    const days = Math.max.apply(null, values);
                    this.loanDays = days;
                    due.max = this.addDays(min, days);
                    due.value = due.max;
                    hint.textContent = 'Otomatis ' + days + ' hari sesuai kategori buku (jika campur, dipakai durasi terpanjang).';
                },
                setStudent(el) {
                    const option = el.options[el.selectedIndex];
                    document.getElementById('res-nis').value = option.dataset.nis || '';
                    document.getElementById('res-kelas').value = option.dataset.class || '';
                    this.sisaPaket = parseInt(option.dataset.sisapaket) || 0;
                    this.sisaBebas = parseInt(option.dataset.sisabebas) || 0;
                    this.quotaPaket = parseInt(option.dataset.quotapaket) || 4;
                    this.quotaBebas = parseInt(option.dataset.quotabebas) || 2;
                    this.aktifPaket = parseInt(option.dataset.aktifpaket) || 0;
                    this.aktifBebas = parseInt(option.dataset.aktifbebas) || 0;
                    this.applyDuration();
                },
                addBook() {
                    if (!this.tempBookId) return;
                    const select = document.getElementById('select-book-raw');
                    const opt = select.options[select.selectedIndex];
                    const loanDays = parseInt(opt.dataset.loandays);
                    if (!isNaN(loanDays) && loanDays < 1) {
                        return alert('Buku kategori ini hanya boleh dibaca di tempat (durasi 0 hari).');
                    }
                    if (this.selectedBooks.find(function (b) { return b.id === this.tempBookId; }.bind(this))) {
                        return alert('Buku sudah dipilih!');
                    }
                    const group = opt.dataset.quotagroup === 'paket' ? 'paket' : 'bebas';
                    const selectedSame = this.selectedBooks.filter(function (b) { return b.quota_group === group; }).length;
                    const sisa = group === 'paket' ? this.sisaPaket : this.sisaBebas;
                    const quota = group === 'paket' ? this.quotaPaket : this.quotaBebas;
                    const aktif = group === 'paket' ? this.aktifPaket : this.aktifBebas;
                    const label = group === 'paket' ? 'buku paket/pelajaran' : 'buku bebas';
                    if (selectedSame >= sisa) {
                        return alert('Sisa kuota ' + label + ' ' + sisa + ' (maks ' + quota + ', sudah pinjam ' + aktif + ').');
                    }
                    this.selectedBooks.push({
                        id: this.tempBookId,
                        title: opt.dataset.title,
                        author: opt.dataset.author,
                        code: opt.dataset.code,
                        category: opt.dataset.category || '-',
                        quota_group: group,
                        loan_days: isNaN(loanDays) ? 7 : loanDays
                    });
                    this.applyDuration();
                    const tsControl = select.tomselect;
                    if (tsControl) { tsControl.clear(); }
                    this.tempBookId = '';
                },
                removeBook(index) {
                    this.selectedBooks.splice(index, 1);
                    this.applyDuration();
                }
            };
        }
    </script>

    <div class="space-y-4" x-data="transactionPage()">
        
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-lg">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
                <p class="text-gray-400 text-[10px] uppercase font-light tracking-tight">Total Transaksi</p>
                <h3 class="text-base font-light text-gray-800">{{ number_format($total_peminjaman ?? 0, 0, ',', '.') }}</h3>
                <p class="text-[9px] text-gray-400 font-light">Semua transaksi</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center text-lg">
                <i class="fas fa-book-reader"></i>
            </div>
            <div>
                <p class="text-gray-400 text-[10px] uppercase font-light tracking-tight">Peminjaman Aktif</p>
                <h3 class="text-base font-light text-gray-800">{{ number_format($peminjaman_aktif ?? 0, 0, ',', '.') }}</h3>
                <p class="text-[9px] text-indigo-600 font-light">{{ $persen_aktif ?? '0' }}% dari total transaksi</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex items-center justify-center text-lg">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <p class="text-gray-400 text-[10px] uppercase font-light tracking-tight">Pengembalian</p>
                <h3 class="text-base font-light text-gray-800">{{ number_format($total_pengembalian ?? 0, 0, ',', '.') }}</h3>
                <p class="text-[9px] text-green-600 font-light">{{ $persen_kembali ?? '0' }}% dari total transaksi</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-lg">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-gray-400 text-[10px] uppercase font-light tracking-tight">Terlambat</p>
                <h3 class="text-base font-light text-gray-800">{{ number_format($total_terlambat ?? 0, 0, ',', '.') }}</h3>
                <p class="text-[9px] text-red-600 font-light">{{ $persen_terlambat ?? '0' }}% dari total transaksi</p>
            </div>
        </div>
    </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-50">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Peminjaman..." 
                            class="w-full pl-3 pr-10 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-300"></i>
                    </div>
                </div>

                <div class="w-36">
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="">Semua Status</option>
                        <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="kembali" {{ request('status') == 'kembali' ? 'selected' : '' }}>Dikembalikan</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-[11px] text-white px-3 py-1.5 rounded-lg font-light hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fas fa-filter text-[10px]"></i> Terapkan Filter
                    </button>
                    
                    <a href="{{ route('admin.transactions.index') }}" class="border border-gray-200 text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-100 transition flex items-center justify-center">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </a>
                </div>
            </form>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-50 flex justify-between items-center text-gray-800 font-light">
                <h2>Daftar Peminjaman <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-[10px] text-gray-400 font-light">{{ $transactions->total() }} data</span></h2>
                <div class="flex gap-2">
                     <button @click="showCreateModal = true" class="bg-[#2563EB] hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg font-light text-[12px] transition-colors flex items-center gap-2 shadow-sm">
                        <i class="fas fa-plus text-[12px]"></i> Tambah Peminjaman
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap text-[11px]">
                    <thead class="bg-gray-50/80 text-gray-500 text-[10px] font-light uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 w-10 text-center">No</th>
                            <th class="px-4 py-2.5">Nama Anggota</th>
                            <th class="px-4 py-2.5">Buku Dipinjam</th>
                            <th class="px-4 py-2.5 text-center">Tgl Pinjam</th>
                            <th class="px-4 py-2.5 text-center">Jatuh Tempo</th>
                            <th class="px-4 py-2.5 text-center w-28">Status</th>
                            <th class="px-4 py-2.5 text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        @forelse($transactions as $index => $trx)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-4 py-2 text-center text-gray-400 font-light">
                                    {{ ($transactions->currentPage() - 1) * $transactions->perPage() + $index + 1 }}.
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-[#2563EB] flex items-center justify-center text-[9px] font-light shrink-0">
                                            {{ strtoupper(substr($trx->student->name ?? 'A', 0, 2)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-light text-gray-800 text-[11px]">{{ $trx->student->name ?? '-' }}</span>
                                            <span class="text-[9px] text-gray-400 font-mono">{{ $trx->transaction_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex flex-col gap-0.5">
                                        @foreach($trx->details->take(2) as $detail)
                                            <span class="text-[10px] text-gray-600 truncate max-w-45"><i class="fas fa-book text-gray-300 mr-1 text-[8px]"></i>{{ $detail->book->title ?? '-' }}</span>
                                        @endforeach
                                        @if($trx->details->count() > 2)
                                            <span class="text-[9px] text-blue-500 font-light">+{{ $trx->details->count() - 2 }} buku lainnya</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-center text-gray-600 font-light">
                                    {{ \Carbon\Carbon::parse($trx->borrow_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 text-center font-light {{ $trx->due_date < now() && $trx->status == 'borrowed' ? 'text-red-500' : 'text-gray-600' }}">
                                    {{ \Carbon\Carbon::parse($trx->due_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @php
                                        $statusClass = 'bg-green-50 text-green-600 border-green-50';
                                        $statusIcon = 'fa-check-circle';
                                        $statusText = 'Dipinjam';
                                        
                                        if($trx->status == 'returned') {
                                            $statusClass = 'bg-gray-100 text-gray-600 border-gray-100';
                                            $statusText = 'Dikembalikan';
                                        } elseif($trx->due_date < now() && $trx->status == 'borrowed') {
                                            $statusClass = 'bg-red-50 text-red-600 border-red-50';
                                            $statusIcon = 'fa-exclamation-triangle';
                                            $statusText = 'Terlambat';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-light border {{ $statusClass }}">
                                        @if($trx->status != 'returned') <i class="fas {{ $statusIcon }} mr-1 mb-px"></i> @endif
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if($trx->status == 'returned')
                                        <span class="text-gray-300 font-light text-lg leading-none">-</span>
                                    @else
                                        <button type="button" @click='openEditModal(@json($trx))'
                                                class="w-6 h-6 rounded bg-amber-50 border border-amber-100 text-amber-500 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-colors mx-auto"
                                                title="Edit Peminjaman">
                                            <i class="fas fa-edit text-[10px]"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400 italic">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-clipboard-list text-2xl mb-2 text-gray-300"></i>
                                        <p class="font-light text-[11px]">Belum ada data peminjaman.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($transactions, 'hasPages') && $transactions->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-2">
                    <div class="text-[10px] text-gray-500 font-light">
                        Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }}
                    </div>
                    <div>
                        {{ $transactions->links('pagination::tailwind') }}
                    </div>
                </div>
            @endif
        </div>

        <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showCreateModal = false" x-show="showCreateModal" x-transition.opacity></div>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
                     x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-white">
                        <h3 class="text-[15px] font-light text-gray-800 flex items-center gap-2"><i class="fas fa-plus-circle text-blue-600"></i> Tambah Peminjaman</h3>
                        <button @click="showCreateModal = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times"></i></button>
                    </div>

                    @if ($errors->any() || session('error'))
                    <div class="px-5 pt-3">
                        <div class="p-3 bg-red-50 border border-red-200 text-red-600 text-[11px] rounded-lg">
                            @if(session('error'))
                                <p class="font-light"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</p>
                            @endif
                            @if($errors->any())
                                <ul class="list-disc pl-4 mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('admin.transactions.store') }}" method="POST" id="form-peminjaman" x-data="borrowForm()">
                        @csrf
                        <div class="p-5 max-h-[75vh] overflow-y-auto bg-gray-50/30 space-y-4">
                            
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                <h4 class="text-[#2563EB] font-light text-[11px] mb-3 border-b border-gray-100 pb-2 uppercase tracking-wide">1. Data Anggota</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="text-[10px] font-light text-gray-700 mb-1 block">Cari Anggota <span class="text-red-500">*</span></label>
                                        <select name="student_id" id="select-student" @change="setStudent($event.target)" required class="w-full border-gray-200 rounded-md text-[11px] focus:ring-blue-500 py-1.5 px-2 bg-gray-50">
                                            <option value="">Pilih nama / NIS anggota...</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" data-nis="{{ $student->nis }}" data-name="{{ $student->name }}" data-class="{{ $student->class }}" data-sisapaket="{{ $student->remainingLoanSlots('paket') }}" data-sisabebas="{{ $student->remainingLoanSlots('bebas') }}" data-quotapaket="{{ $student->loanQuota('paket') }}" data-quotabebas="{{ $student->loanQuota('bebas') }}" data-aktifpaket="{{ $student->activeBorrowedCopies('paket') }}" data-aktifbebas="{{ $student->activeBorrowedCopies('bebas') }}">
                                                    {{ $student->nis }} - {{ $student->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-light text-gray-500 mb-1 block">NIS</label>
                                        <input type="text" id="res-nis" readonly class="w-full bg-gray-100 border-transparent rounded-md text-[11px] text-gray-600 py-1.5 px-2 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-light text-gray-500 mb-1 block">Kelas</label>
                                        <input type="text" id="res-kelas" readonly class="w-full bg-gray-100 border-transparent rounded-md text-[11px] text-gray-600 py-1.5 px-2 outline-none">
                                    </div>
                                    <input type="hidden" id="res-nama">
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                <div class="flex justify-between items-end mb-3 border-b border-gray-100 pb-2">
                                    <h4 class="text-[#2563EB] font-light text-[11px] uppercase tracking-wide">2. Pilih Buku</h4>
                                    <span class="text-[9px] font-light bg-blue-50 text-blue-600 px-2 py-0.5 rounded" x-text="'Paket sisa ' + sisaPaket + '/' + quotaPaket + ' · Bebas sisa ' + sisaBebas + '/' + quotaBebas"></span>
                                </div>
                                
                                <div class="flex gap-2 mb-3">
                                    <div class="flex-1">
                                        <select id="select-book-raw" x-model="tempBookId" class="w-full border-gray-200 rounded-md text-[11px] py-1.5 px-2 bg-gray-50 focus:ring-blue-500">
                                            <option value="">Ketik judul / kode buku...</option>
                                            @foreach($books as $book)
                                                <option value="{{ $book->id }}" data-title="{{ $book->title }}" data-author="{{ $book->author }}" data-code="{{ $book->book_code }}" data-category="{{ $book->category->name ?? '-' }}" data-loandays="{{ $book->category->loan_days ?? 7 }}" data-quotagroup="{{ $book->category->quotaGroup() }}">
                                                    {{ $book->book_code }} - {{ $book->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" @click="addBook()" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-1.5 rounded-md font-light text-[10px] transition-colors whitespace-nowrap self-start mt-0.5">
                                        <i class="fas fa-plus mr-1"></i> Tambah
                                    </button>
                                </div>

                                <div class="border border-gray-100 rounded-md overflow-hidden">
                                    <table class="w-full text-left text-[10px]">
                                        <thead class="bg-gray-50 text-gray-500 uppercase">
                                            <tr>
                                                <th class="px-3 py-1.5 w-8">No</th>
                                                <th class="px-3 py-1.5">Kode & Judul Buku</th>
                                                <th class="px-3 py-1.5 text-center w-16">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="(book, index) in selectedBooks" :key="index">
                                                <tr class="hover:bg-gray-50/50">
                                                    <td class="px-3 py-2 text-gray-400" x-text="index + 1"></td>
                                                    <td class="px-3 py-2">
                                                        <p class="font-light text-gray-700" x-text="book.title"></p>
                                                        <p class="text-[9px] text-gray-400" x-text="'Kode: ' + book.code + ' | ' + (book.quota_group === 'paket' ? 'Paket' : 'Bebas') + ' | ' + book.loan_days + ' hari'"></p>
                                                        <input type="hidden" name="book_id[]" :value="book.id">
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <button type="button" @click="removeBook(index)" class="text-red-500 hover:bg-red-50 p-1 rounded transition-colors"><i class="fas fa-times"></i></button>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-if="selectedBooks.length === 0">
                                                <tr><td colspan="3" class="px-3 py-4 text-center text-gray-400 italic text-[10px]">Belum ada buku yang ditambahkan.</td></tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                <h4 class="text-[#2563EB] font-light text-[11px] mb-3 border-b border-gray-100 pb-2 uppercase tracking-wide">3. Durasi Peminjaman</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="text-[10px] font-light text-gray-700 mb-1 block">Tgl Pinjam <span class="text-red-500">*</span></label>
                                        <input type="date" name="borrow_date" value="{{ date('Y-m-d') }}" readonly
                                            class="w-full border-gray-200 rounded-md text-[11px] py-1.5 px-2 bg-gray-100 text-gray-600 cursor-not-allowed pointer-events-none">
                                        <p class="text-[9px] text-gray-400 mt-1">Otomatis hari ini</p>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-light text-gray-700 mb-1 block">Jatuh Tempo <span class="text-red-500">*</span></label>
                                        <input type="date" name="due_date" id="due_date_input"
                                            min="{{ date('Y-m-d') }}"
                                            required readonly
                                            class="w-full border-gray-200 rounded-md text-[11px] py-1.5 px-2 bg-gray-100 text-gray-600 cursor-not-allowed">
                                        <p id="due_date_hint" class="text-[9px] text-gray-400 mt-1">Jatuh tempo dihitung otomatis dari kategori buku.</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-light text-gray-700 mb-1 block">Catatan <span class="font-normal text-gray-400">(Opsional)</span></label>
                                    <textarea name="notes" rows="1" class="w-full border-gray-200 rounded-md text-[11px] py-1.5 px-2 bg-gray-50 focus:ring-blue-500" placeholder="Ketik catatan..."></textarea>
                                </div>
                            </div>

                        </div>

                        <div class="px-5 py-3 bg-white flex justify-end gap-2 border-t border-gray-100">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-1.5 text-[11px] font-light text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                            <button type="submit" class="bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-1.5 rounded-lg font-light text-[11px] transition-colors flex items-center gap-1.5"><i class="fas fa-save text-[10px]"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showEditModal = false" x-show="showEditModal" x-transition.opacity></div>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full"
                     x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-white">
                        <h3 class="text-[15px] font-light text-gray-800 flex items-center gap-2"><i class="fas fa-edit text-amber-500"></i> Edit Transaksi</h3>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times"></i></button>
                    </div>

                    <form :action="'{{ route('admin.transactions.update', 'REPLACE_ID') }}'.replace('REPLACE_ID', editForm.id)" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-5 space-y-4 bg-gray-50/30">
                            
                            <div class="bg-blue-50 border border-blue-100 p-3 rounded-lg flex justify-between items-center">
                                <div>
                                    <p class="text-[9px] uppercase font-light text-blue-500">No. Transaksi</p>
                                    <p class="text-[11px] font-light text-blue-900" x-text="editForm.transaction_code"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] uppercase font-light text-blue-500">Anggota</p>
                                    <p class="text-[11px] font-light text-blue-900 truncate max-w-37.5" x-text="editForm.student_name"></p>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm space-y-3">
                                <div>
                                    <label class="text-[10px] font-light text-gray-700 mb-1 block">Tgl Pinjam</label>
                                    <input type="date" name="borrow_date" x-model="editForm.borrow_date" readonly
                                        class="w-full border-gray-200 rounded-md text-[11px] py-1.5 px-2 bg-gray-100 text-gray-600 cursor-not-allowed pointer-events-none">
                                    <p class="text-[9px] text-gray-400 mt-1">Tanggal pinjam tidak diubah</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-light text-gray-700 mb-1 block">Jatuh Tempo</label>
                                    <input type="date" name="due_date" x-model="editForm.due_date" required
                                        :min="editForm.borrow_date"
                                        :max="maxDueFrom(editForm.borrow_date)"
                                        class="w-full border-gray-200 rounded-md text-[11px] py-1.5 px-2 bg-gray-50 focus:ring-blue-500">
                                    <p class="text-[9px] text-gray-400 mt-1" x-text="'Maksimal ' + (editForm.loan_days || 7) + ' hari sesuai kategori buku.'"></p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-light text-gray-700 mb-1 block">Catatan</label>
                                    <textarea name="notes" x-model="editForm.notes" rows="2" class="w-full border-gray-200 rounded-md text-[11px] py-1.5 px-2 bg-gray-50 focus:ring-blue-500"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-3 bg-white flex justify-end gap-2 border-t border-gray-100">
                            <button type="button" @click="showEditModal = false" class="px-4 py-1.5 text-[11px] font-light text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-1.5 rounded-lg font-light text-[11px] transition-colors flex items-center gap-1.5"><i class="fas fa-save text-[10px]"></i> Perbarui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi untuk pilihan anggota (siswa)
            new TomSelect("#select-student", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                onChange: function() {
                    const el = document.getElementById('select-student');
                    const form = document.getElementById('form-peminjaman');
                    if (form && window.Alpine) {
                        Alpine.$data(form).setStudent(el);
                    }
                }
            });

            // Inisialisasi untuk pilihan buku yang dipinjam
            new TomSelect("#select-book-raw", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>
</x-layouts.app>