<x-layouts.app title="Info Denda - Perpustakaan SMK Budi Mulia">
    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        [x-cloak] { display: none !important; }
    </style>
    @endsection

    <div class="max-w-7xl mx-auto px-6 py-6 space-y-6 min-h-screen pb-16">
        
        {{-- HEADER HALAMAN & 4 COUNTER CARD ATAS --}}
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
            <div class="shrink-0">
                <h2 class="text-xl font-medium text-blue-800">Info Denda</h2>
                <p class="text-[11px] text-gray-400 mt-0.5">Informasi denda yang perlu Anda bayar beserta riwayat pembayarannya.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 w-full xl:w-auto flex-1 max-w-4xl">
                <div class="bg-white p-3 rounded-2xl border border-gray-150 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-500 text-white flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-400 font-medium leading-none">Total Denda</span>
                        <span class="text-sm font-medium text-gray-800 block mt-1 leading-none">Rp {{ number_format($stats['total_belum_lunas'] + $stats['total_dibayar'], 0, ',', '.') }}</span>
                        <span class="text-[8px] text-gray-400 block mt-0.5">Total semua denda</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-2xl border border-gray-150 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-400 font-medium leading-none">Denda Belum Dibayar</span>
                        <span class="text-sm font-medium text-gray-800 block mt-1 leading-none">Rp {{ number_format($stats['total_belum_lunas'], 0, ',', '.') }}</span>
                        <span class="text-[8px] text-amber-600 font-medium block mt-0.5">{{ $stats['jumlah_tanggungan'] }} denda belum dibayar</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-2xl border border-gray-150 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-green-600 text-white flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-400 font-medium leading-none">Denda Lunas</span>
                        <span class="text-sm font-medium text-gray-800 block mt-1 leading-none">Rp {{ number_format($stats['total_dibayar'], 0, ',', '.') }}</span>
                        <span class="text-[8px] text-gray-400 block mt-0.5">0 denda telah lunas</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-2xl border border-gray-150 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shrink-0">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-gray-400 font-medium leading-none">Total Transaksi</span>
                        <span class="text-sm font-medium text-gray-800 block mt-1 leading-none">1</span>
                        <span class="text-[8px] text-gray-400 block mt-0.5">Total transaksi denda</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- LAYOUT UTAMA: SIDEBAR INFO (KIRI) & DAFTAR TABEL DENDA (KANAN) --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            {{-- BLOCK SIDEBAR KIRI --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-150 shadow-sm text-center space-y-3">
                    <h3 class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Ringkasan Denda</h3>
                    <div class="py-2">
                        <p class="text-[10px] text-red-500 font-medium uppercase tracking-wide">Denda Belum Dibayar</p>
                        <p class="text-xl font-medium text-red-600 mt-1">Rp {{ number_format($stats['total_belum_lunas'], 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-150 shadow-sm">
                    <h4 class="text-xs font-medium text-gray-700 uppercase tracking-wide mb-3">Informasi Denda</h4>
                    <ul class="text-[11px] text-gray-500 space-y-2.5 leading-relaxed font-medium">
                        @php
                            $dendaPerHari = $fineSetting->late_fee_per_day ?? 500;
                        @endphp
                        <li class="flex items-start gap-2">
                            <i class="fas fa-bell text-blue-500 mt-0.5 shrink-0"></i>
                            <span>Denda keterlambatan: <span class="font-medium text-gray-700">Rp {{ number_format($dendaPerHari, 0, ',', '.') }} / hari / buku</span></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-plus-circle text-red-400 mt-0.5 shrink-0"></i>
                            <span>Denda akan bertambah setiap hari jika buku belum dikembalikan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 shrink-0"></i>
                            <span>Silakan lakukan pembayaran denda sebelum melakukan peminjaman baru.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-university text-gray-400 mt-0.5 shrink-0"></i>
                            <span>Pembayaran dapat dilakukan di perpustakaan atau melalui petugas.</span>
                        </li>
                    </ul>
                </div>                
            </div>

            {{-- BLOCK TABEL KANAN --}}
            <div class="lg:col-span-3 space-y-6">
                
                <div class="bg-white p-5 rounded-2xl border border-gray-150 shadow-sm space-y-4">
                    <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wide">Denda Belum Dibayar</h3>
                    
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse whitespace-nowrap text-[11px]">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70 text-gray-500 uppercase tracking-wider font-medium">
                                    <th class="px-3 py-2.5 text-center w-12">No</th>
                                    <th class="px-4 py-2.5 w-4/12">Buku</th>
                                    <th class="px-4 py-2.5">Tanggal Pinjam</th>
                                    <th class="px-4 py-2.5">Tanggal Jatuh Tempo</th>
                                    <th class="px-4 py-2.5">Terlambat</th>
                                    <th class="px-4 py-2.5">Denda / Hari</th>
                                    <th class="px-4 py-2.5">Total Denda</th>
                                    <th class="px-4 py-2.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($unpaidFines as $index => $fine)
                                    @php
                                        $bookDetail = $fine->borrowingDetails->first()?->book;
                                        $dendaPerHari = $fineSetting->late_fee_per_day ?? 500;
                                        $totalDenda = $fine->fine->total_fine ?? 0;
                                        $lateDays = ($dendaPerHari > 0 && $totalDenda > 0) ? floor($totalDenda / $dendaPerHari) : 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50/40 transition-colors">
                                        <td class="px-3 py-3 text-center text-gray-400 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-3 items-center">
                                                <div class="w-8 h-11 bg-gray-100 rounded overflow-hidden shrink-0 shadow-sm border border-gray-200">
                                                    @if($bookDetail && $bookDetail->cover_image)
                                                        <img src="{{ asset('covers/' . $bookDetail->cover_image) }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50"><i class="fas fa-book"></i></div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h4 class="font-medium text-gray-800 text-[12px] leading-snug">{{ $bookDetail->title ?? 'Judul Buku' }}</h4>
                                                    <span class="text-[9px] font-medium text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded mt-0.5 inline-block">{{ $bookDetail->category->name ?? 'Basis Data' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 font-medium">{{ \Carbon\Carbon::parse($fine->borrow_date)->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-3 text-gray-600 font-medium">{{ \Carbon\Carbon::parse($fine->due_date)->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-3 text-red-600 font-medium">{{ $lateDays }} hari</td>
                                        <td class="px-4 py-3 text-gray-500">Rp {{ number_format($dendaPerHari, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-red-600 font-medium">Rp {{ number_format($fine->fine->total_fine ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="bg-red-50 text-red-600 border border-red-100 text-[9px] font-medium px-2 py-0.5 rounded-md">Belum Dibayar</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">Tidak ada tanggungan denda belum dibayar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100/50 flex justify-between items-center px-4">
                        <span class="text-blue-900 font-medium text-[11px]">Total Denda Belum Dibayar</span>
                        <span class="text-red-600 font-medium text-xs">Rp {{ number_format($stats['total_belum_lunas'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-150 shadow-sm space-y-4">
                    <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wide">Riwayat Pembayaran Denda</h3>
                    
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse whitespace-nowrap text-[11px]">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70 text-gray-500 uppercase tracking-wider font-medium">
                                    <th class="px-3 py-2.5 text-center w-12">No</th>
                                    <th class="px-4 py-2.5 w-3/12">Buku</th>
                                    <th class="px-4 py-2.5">Tanggal Pinjam</th>
                                    <th class="px-4 py-2.5">Tanggal Kembali</th>
                                    <th class="px-4 py-2.5">Terlambat</th>
                                    <th class="px-4 py-2.5">Total Denda</th>
                                    <th class="px-4 py-2.5">Tanggal Bayar</th>
                                    <th class="px-4 py-2.5">Metode Pembayaran</th>
                                    <th class="px-4 py-2.5">Status</th>
                                    <th class="px-4 py-2.5">Bukti</th>
                                    <th class="px-4 py-2.5">Petugas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($paidFines as $index => $history)
                                    @php
                                        $bookDetail = $history->borrowingDetails->first()?->book;
                                        $dendaPerHari = $fineSetting->late_fee_per_day ?? 500;
                                        $totalDenda = $history->fine->total_fine ?? 0;
                                        $lateDays = ($dendaPerHari > 0 && $totalDenda > 0) ? floor($totalDenda / $dendaPerHari) : 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50/40 transition-colors">
                                        <td class="px-3 py-4 text-center text-gray-400">{{ $paidFines->firstItem() + $index }}</td>
                                        <td class="px-4 py-4 text-gray-800 font-medium">{{ $bookDetail->title ?? '-' }}</td>
                                        <td class="px-4 py-4 text-gray-500">{{ \Carbon\Carbon::parse($history->borrow_date)->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-4 text-gray-500">{{ \Carbon\Carbon::parse($history->return_date)->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-4 text-gray-600">{{ $lateDays }} hari</td>
                                        <td class="px-4 py-4 text-gray-800 font-medium">Rp {{ number_format($totalDenda, 0, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-gray-500">{{ \Carbon\Carbon::parse($history->fine->updated_at)->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-4 text-gray-600">Tunai</td>
                                        <td class="px-4 py-4">
                                            <span class="bg-green-50 text-green-600 border border-green-100 text-[9px] font-medium px-2 py-0.5 rounded-md">Lunas</span>
                                        </td>
                                        <td class="px-4 py-4 text-blue-600 font-medium hover:underline cursor-pointer"><i class="fas fa-file-alt mr-1"></i> Lihat</td>
                                        <td class="px-4 py-4 text-gray-600">Petugas Perpus</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-5 py-12 text-center text-gray-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-file-invoice text-3xl text-gray-200 mb-2"></i>
                                                <p class="font-medium text-gray-700 text-xs">Belum ada riwayat pembayaran denda.</p>
                                                <p class="text-[10px] text-gray-400 mt-0.5">Riwayat pembayaran denda Anda akan muncul di sini setelah melakukan pembayaran.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 pt-2 border-t border-gray-50">
                        {{ $paidFines->links() }}
                    </div>
                </div>

                {{-- Alert Info Footer --}}
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-2.5 text-blue-700 text-[11px] font-medium shadow-sm">
                    <i class="fas fa-info-circle text-xs shrink-0"></i>
                    <p>Pastikan semua denda telah dibayar untuk menghindari pembatasan peminjaman buku.</p>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>