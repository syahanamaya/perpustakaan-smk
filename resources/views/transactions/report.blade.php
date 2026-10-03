<x-layouts.app title="Laporan Tahunan">
    <div class="space-y-6">
        <!-- Header Laporan -->
        <div class="flex justify-between items-end bg-white p-6 rounded-xl border border-gray-200 shadow-sm print:hidden">
            <div>
                <h1 class="text-3xl font-black text-gray-900 uppercase">Laporan Sirkulasi</h1>
                <p class="text-gray-500">Rekapitulasi data peminjaman buku perpustakaan.</p>
            </div>
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak PDF
            </button>
        </div>

        <!-- Tabel Laporan (Full Width) -->
        <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm">
            <!-- Kop Surat (Hanya muncul saat print) -->
            <div class="hidden print:block text-center mb-8 border-b-2 border-black pb-4">
                <h2 class="text-2xl font-bold">PERPUSTAKAAN SMK NEGERI</h2>
                <p>Jl. Pendidikan No. 123, Kota Perpustakaan</p>
                <h3 class="text-xl font-bold mt-4">LAPORAN DATA SIRKULASI BUKU</h3>
            </div>

            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="py-3 text-left">TANGGAL</th>
                        <th class="py-3 text-left">NAMA SISWA</th>
                        <th class="py-3 text-left">JUDUL BUKU</th>
                        <th class="py-3 text-center">DENDA</th>
                        <th class="py-3 text-center">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transactions as $trx)
                    <tr>
                        <td class="py-4">{{ $trx->borrow_date->format('d/m/Y') }}</td>
                        <td class="py-4 font-bold">{{ $trx->student->name }}</td>
                        <td class="py-4 italic">
                            @foreach($trx->details as $d) {{ $d->book->title }}{{ !$loop->last ? ', ' : '' }} @endforeach
                        </td>
                        <td class="py-4 text-center">Rp {{ number_format($trx->total_fine, 0, ',', '.') }}</td>
                        <td class="py-4 text-center uppercase font-bold text-xs">{{ $trx->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <style>
        @media print {
            aside, header, button, .print\:hidden { display: none !important; }
            body { background: white; padding: 0; }
            .shadow-sm { box-shadow: none !important; border: none !important; }
        }
    </style>
</x-layouts.app>