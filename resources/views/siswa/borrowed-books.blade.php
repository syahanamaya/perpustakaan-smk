<x-layouts.app title="Peminjaman Saya - Perpustakaan SMK Budi Mulia">
    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        [x-cloak] { display: none !important; }
    </style>
    @endsection

    {{-- Management data menggunakan Alpine.js --}}
    <div x-data="{ openModal: false, selectedBorrow: {} }" class="max-w-7xl mx-auto px-6 py-6 space-y-6 min-h-screen pb-16">
        
        {{-- BARIS ATAS: Judul Halaman & 4 Counter Card Statistik --}}
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
            <div class="shrink-0">
                <h2 class="text-xl font-medium text-blue-800">Peminjaman Saya</h2>
                <p class="text-[11px] text-gray-400 mt-0.5">Kelola semua peminjaman buku yang sedang Anda lakukan.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 w-full xl:w-auto flex-1 max-w-4xl">
                <div class="bg-white p-3 rounded-2xl border border-gray-150 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-400 font-medium leading-none">Buku Dipinjam</span>
                        <span class="text-lg font-medium text-gray-800 block mt-1 leading-none">{{ $stats['dipinjam'] }}</span>
                        <span class="text-[9px] text-gray-400 block mt-0.5">Sedang dipinjam</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-2xl border border-gray-150 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-400 font-medium leading-none">Menunggu</span>
                        <span class="text-lg font-medium text-gray-800 block mt-1 leading-none">{{ $stats['menunggu'] }}</span>
                        <span class="text-[9px] text-gray-400 block mt-0.5">Menunggu persetujuan</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-2xl border border-gray-150 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-green-600 text-white flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-400 font-medium leading-none">Jatuh Tempo Terdekat</span>
                        <span class="text-sm font-medium text-gray-800 block mt-1 leading-none">{{ $jatuhTempo['teks'] }}</span>
                        <span class="text-[8px] text-gray-400 block mt-0.5">{{ $jatuhTempo['tanggal'] }}</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-2xl border border-gray-150 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-500 text-white flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-400 font-medium leading-none">Terlambat</span>
                        <span class="text-lg font-medium text-gray-800 block mt-1 leading-none">{{ $stats['terlambat'] }}</span>
                        <span class="text-[9px] text-red-500 font-medium block mt-0.5">Harap segera kembalikan</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- LAYOUT UTAMA: SIDEBAR FILTER (KIRI) & DAFTAR TABEL PEMINJAMAN (KANAN) --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            {{-- BLOCK SIDEBAR KIRI --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-150 shadow-sm space-y-1">
                    <h3 class="text-[11px] font-medium text-gray-400 uppercase tracking-wider px-2 mb-3">Filter Status</h3>
                    
                @php $currentStatus = $statusFilter ?? 'semua'; @endphp

                <a href="{{ route('student.borrowed-books', ['status' => 'semua']) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentStatus == 'semua' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-th-list text-[11px]"></i> Semua Status</span>
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $currentStatus == 'semua' ? 'bg-blue-200/60 text-blue-800' : 'bg-gray-100 text-gray-600' }}">{{ $stats['total'] }}</span>
                    </a>

                <a href="{{ route('student.borrowed-books', ['status' => 'dipinjam']) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentStatus == 'dipinjam' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-book-reader text-[11px]"></i> Sedang Dipinjam</span>
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $currentStatus == 'dipinjam' ? 'bg-blue-200/60 text-blue-800' : 'bg-gray-100 text-gray-600' }}">{{ $stats['dipinjam'] }}</span>
                    </a>

                <a href="{{ route('student.borrowed-books', ['status' => 'menunggu']) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentStatus == 'menunggu' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-hourglass-start text-[11px]"></i> Menunggu Persetujuan</span>
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $currentStatus == 'menunggu' ? 'bg-blue-200/60 text-blue-800' : 'bg-gray-100 text-gray-600' }}">{{ $stats['menunggu'] }}</span>
                    </a>

                <a href="{{ route('student.borrowed-books', ['status' => 'selesai']) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentStatus == 'selesai' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-history text-[11px]"></i> Dikembalikan</span>
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $currentStatus == 'selesai' ? 'bg-blue-200/60 text-blue-800' : 'bg-gray-100 text-gray-600' }}">{{ $stats['selesai'] }}</span>
                    </a>

                <a href="{{ route('student.borrowed-books', ['status' => 'terlambat']) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentStatus == 'terlambat' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-exclamation-circle text-[11px]"></i> Terlambat</span>
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $currentStatus == 'terlambat' ? 'bg-blue-200/60 text-blue-800' : 'bg-gray-100 text-gray-600' }}">{{ $stats['terlambat'] }}</span>
                    </a>
                </div>

                <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100 shadow-sm relative overflow-hidden">
                    <h4 class="text-xs font-medium text-blue-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i> Informasi Peminjaman
                    </h4>
                    <ul class="text-[11px] text-blue-800 space-y-2 list-disc list-inside leading-relaxed font-medium">
                        <li>Maks. buku paket/pelajaran: <span class="font-medium">{{ auth()->guard('student')->user()->loanQuota('paket') }} buku</span></li>
                        <li>Maks. buku bebas (novel/komik/umum): <span class="font-medium">{{ auth()->guard('student')->user()->loanQuota('bebas') }} buku</span></li>
                        <li>Lama peminjaman normal: <span class="font-medium">7 hari</span></li>
                        <li>Denda keterlambatan: <span class="font-medium text-red-600">Rp {{ number_format($fineSetting->late_fee_per_day ?? 500, 0, ',', '.') }}</span> / hari / buku</li>
                    </ul>
                    <div class="mt-4 flex justify-center opacity-85">
                        <i class="fas fa-book-open text-5xl text-blue-200/60"></i>
                    </div>
                </div>
            </div>

            {{-- BLOCK TABEL KANAN --}}
            <div class="lg:col-span-3 bg-white p-5 rounded-2xl border border-gray-150 shadow-sm space-y-4">
                <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wide">Daftar Peminjaman</h3>
                
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse whitespace-nowrap text-[11px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-gray-500 uppercase tracking-wider font-medium">
                                <th class="px-4 py-3 w-5/12">Buku</th>
                                <th class="px-4 py-3">Tanggal Pinjam</th>
                                <th class="px-4 py-3">Jatuh Tempo</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Denda</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- Baris Iterasi Data Gabungan Aktif + Riwayat --}}
                            @foreach($activeBorrowings as $transaction)
                                @php
                                    $bookDetail = $transaction->borrowingDetails->first()?->book;
                                    $dueDate = \Carbon\Carbon::parse($transaction->due_date);
                                    $diffDays = now()->startOfDay()->diffInDays($dueDate->startOfDay(), false);
                                @endphp
                                <tr class="hover:bg-gray-50/40 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="flex gap-3 items-center">
                                            <div class="w-10 h-14 bg-gray-100 rounded-lg overflow-hidden shrink-0 shadow-sm border border-gray-200">
                                                @if($bookDetail && $bookDetail->cover_image)
                                                    <img src="{{ asset('covers/' . $bookDetail->cover_image) }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50"><i class="fas fa-book"></i></div>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-800 text-[12px] leading-snug">{{ $bookDetail->title ?? 'Judul Buku' }}</h4>
                                                <p class="text-gray-400 text-[10px] mt-0.5">{{ $bookDetail->author ?? '-' }}</p>
                                                <span class="text-[9px] font-medium text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded mt-1 inline-block">{{ $bookDetail->category->name ?? 'Umum' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-4 py-3.5 text-gray-600 font-medium">
                                        {{ \Carbon\Carbon::parse($transaction->borrow_date)->translatedFormat('d M Y') }}
                                    </td>
                                    
                                    <td class="px-4 py-3.5">
                                        @if($diffDays >= 0)
                                            <p class="text-red-600 font-medium">{{ $dueDate->translatedFormat('d M Y') }}</p>
                                            <p class="text-gray-400 text-[10px] mt-0.5">{{ $diffDays }} hari lagi</p>
                                        @else
                                            <p class="text-red-600 font-medium">{{ $dueDate->translatedFormat('d M Y') }}</p>
                                            <p class="text-red-500 font-medium text-[10px] mt-0.5">Terlambat {{ abs($diffDays) }} hari</p>
                                        @endif
                                    </td>
                                    
                                    <td class="px-4 py-3.5">
                                        @if($transaction->status == 'pending')
                                            <span class="bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-medium px-2 py-0.5 rounded-md">Menunggu</span>
                                        @elseif($transaction->status == 'borrowed')
                                            @if($diffDays >= 0)
                                                <span class="bg-blue-50 text-blue-600 border border-blue-100 text-[10px] font-medium px-2 py-0.5 rounded-md">Sedang Dipinjam</span>
                                            @else
                                                <span class="bg-red-50 text-red-600 border border-red-100 text-[10px] font-medium px-2 py-0.5 rounded-md animate-pulse">Terlambat (Belum Kembali)</span>
                                            @endif
                                        @elseif($transaction->status == 'returned')
                                            <span class="bg-green-50 text-green-600 border border-green-100 text-[10px] font-medium px-2 py-0.5 rounded-md">Dikembalikan</span>
                                        @elseif($transaction->status == 'late')
                                            <span class="bg-red-50 text-red-600 border border-red-100 text-[10px] font-medium px-2 py-0.5 rounded-md">Terlambat (Dikembalikan)</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-4 py-3.5">
                                        @if($transaction->fine && $transaction->fine->total_fine > 0)
                                            <span class="font-medium text-red-600 block">Rp{{ number_format($transaction->fine->total_fine, 0, ',', '.') }}</span>
                                            @if($transaction->fine->fine_status == 'unpaid')
                                                <span class="text-[9px] text-red-500 font-medium">Belum Lunas</span>
                                            @elseif($transaction->fine->fine_status == 'paid')
                                                <span class="text-[9px] text-green-500 font-medium">Lunas</span>
                                            @endif
                                        @elseif($diffDays < 0 && $transaction->status == 'borrowed')
                                            <span class="font-medium text-red-600 block">Rp{{ number_format(abs($diffDays) * 500, 0, ',', '.') }}</span>
                                            <span class="text-[9px] text-gray-400 font-medium">Potensi Denda</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-4 py-3.5 text-center space-y-1">
                                        <button @click="selectedBorrow = {
                                                    code: '{{ $transaction->transaction_code }}',
                                                    title: '{{ $bookDetail->title ?? 'Judul Tidak Tersedia' }}',
                                                    author: '{{ $bookDetail->author ?? '-' }}',
                                                    isbn: '{{ $bookDetail->isbn ?? '-' }}',
                                                    borrow_date: '{{ \Carbon\Carbon::parse($transaction->borrow_date)->translatedFormat('d F Y') }}',
                                                    due_date: '{{ $dueDate->translatedFormat('d F Y') }}',
                                                    status: 'Dipinjam',
                                                    note: '{{ $transaction->notes ?? 'Tidak ada catatan tambahan.' }}'
                                                }; openModal = true" 
                                                class="w-20 py-1 text-center text-blue-600 border border-blue-200 hover:bg-blue-50 font-medium rounded-lg text-[10px] block mx-auto transition-all">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Kontrol Navigasi Pagination Halaman Bawah Tengah --}}
                <div class="mt-6 flex justify-center">
                    {{ $activeBorrowings->links() }}
                </div>
                
                {{-- Alert Info Footer Meja Sirkulasi --}}
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-2.5 text-blue-700 text-[11px] font-medium shadow-sm">
                    <i class="fas fa-info-circle text-xs shrink-0"></i>
                    <p>Jika terdapat kesalahan data atau buku yang belum dikembalikan, segera hubungi petugas perpustakaan.</p>
                </div>
            </div>

        </div>

        {{-- COMPONENT MODAL DETAIL POP-UP --}}
        <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
            <div @click.outside="openModal = false" class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 border border-gray-100 relative">
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                    <div>
                        <span class="text-[9px] uppercase font-medium text-gray-400 tracking-wider block">Kode Transaksi</span>
                        <h3 class="text-sm font-medium text-blue-700 font-mono" x-text="selectedBorrow.code"></h3>
                    </div>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-full hover:bg-gray-100 transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-3.5 text-[11px]">
                    <div>
                        <label class="text-gray-400 font-medium block mb-0.5">Judul Buku</label>
                        <p class="text-xs font-medium text-gray-800" x-text="selectedBorrow.title"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-400 font-medium block mb-0.5">Pengarang</label>
                            <p class="font-medium text-gray-700" x-text="selectedBorrow.author"></p>
                        </div>
                        <div>
                            <label class="text-gray-400 font-medium block mb-0.5">ISBN</label>
                            <p class="font-mono text-gray-700" x-text="selectedBorrow.isbn"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <div>
                            <label class="text-gray-500 font-medium block mb-0.5">Tanggal Pinjam</label>
                            <p class="font-medium text-gray-800" x-text="selectedBorrow.borrow_date"></p>
                        </div>
                        <div>
                            <label class="text-gray-500 font-medium block mb-0.5">Batas Jatuh Tempo</label>
                            <p class="font-medium text-red-600" x-text="selectedBorrow.due_date"></p>
                        </div>
                    </div>

                    <div>
                        <label class="text-gray-400 font-medium block mb-0.5">Catatan Tambahan</label>
                        <p class="text-gray-600 italic" x-text="selectedBorrow.note"></p>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-gray-100 flex justify-end">
                    <button @click="openModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2 rounded-xl text-xs transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div> 
</x-layouts.app>