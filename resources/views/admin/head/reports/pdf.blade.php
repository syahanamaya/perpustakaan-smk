<!DOCTYPE html>
<html>
<head>
    <title>Laporan Perpustakaan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2 class="text-center">Laporan {{ ucwords(str_replace('-', ' ', $type ?? 'Perpustakaan')) }}</h2>
    <p class="text-center">Tanggal Dicetak: {{ date('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                @if(isset($headers))
                    <th>No</th>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                @else
                    <th>No</th>
                    <th>Kode Transaksi</th>
                    <th>Nama Siswa</th>
                    <th>Tanggal Pinjam</th>
                    <th>Status</th>
                    <th>Denda</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php $items = $data ?? $transactions ?? []; @endphp
            @forelse($items as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                @if(isset($type))
                    @if($type === 'peminjaman' || $type === 'pengembalian')
                        <td>{{ $row->transaction_code ?? '-' }}</td>
                        <td>{{ $row->student->name ?? '-' }}</td>
                        <td>{{ $row->borrow_date ? \Carbon\Carbon::parse($row->borrow_date)->format('d M Y') : '-' }}</td>
                        <td>{{ $row->return_date ? \Carbon\Carbon::parse($row->return_date)->format('d M Y') : ($row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d M Y') : '-') }}</td>
                        <td class="text-center">{{ ucfirst($row->status ?? '-') }}</td>
                    @elseif($type === 'riwayat transaksi')
                        <td>{{ $row->transaction_code ?? '-' }}</td>
                        <td>{{ $row->student->name ?? '-' }}</td>
                        <td>{{ $row->borrow_date ? \Carbon\Carbon::parse($row->borrow_date)->format('d M Y') : '-' }}</td>
                        <td>{{ $row->return_date ? \Carbon\Carbon::parse($row->return_date)->format('d M Y') : ($row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d M Y') : '-') }}</td>
                        <td class="text-center">{{ ucfirst($row->status ?? '-') }}</td>
                        <td>Rp {{ number_format($row->fine->total_fine ?? 0, 0, ',', '.') }}</td>
                    @elseif($type === 'denda' || $type === 'rekap denda')
                        <td>{{ $row->borrowing->transaction_code ?? '-' }}</td>
                        <td>{{ $row->borrowing->student->name ?? '-' }}</td>
                        <td>Rp {{ number_format($row->total_fine ?? 0, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row->denda_dibayar ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">{{ ucfirst($row->fine_status ?? '-') }}</td>
                    @elseif($type === 'anggota' || $type === 'siswa')
                        <td>{{ $row->nis ?? '-' }}</td>
                        <td>{{ $row->name ?? '-' }}</td>
                        <td>{{ $row->class ?? '-' }}</td>
                        <td>{{ $row->major ?? '-' }}</td>
                        <td class="text-center">{{ ucfirst($row->status ?? '-') }}</td>
                    @elseif($type === 'koleksi' || $type === 'buku')
                        <td>{{ $row->book_code ?? '-' }}</td>
                        <td>{{ $row->title ?? '-' }}</td>
                        <td>{{ $row->category->name ?? '-' }}</td>
                        <td>{{ $row->rak->nama_rak ?? '-' }}</td>
                        <td class="text-center">{{ $row->stock ?? 0 }}</td>
                    @else
                        <td colspan="{{ count($headers ?? []) }}">-</td>
                    @endif
                @else
                    <td>{{ $row->transaction_code ?? '-' }}</td>
                    <td>{{ $row->student->name ?? '-' }}</td>
                    <td>{{ $row->borrow_date ? \Carbon\Carbon::parse($row->borrow_date)->format('d M Y') : '-' }}</td>
                    <td class="text-center">{{ ucfirst($row->status ?? '-') }}</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ isset($headers) ? count($headers) + 1 : 5 }}" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
