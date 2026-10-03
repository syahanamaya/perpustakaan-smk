<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stok Opname</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; font-size: 9px; }
        .text-center { text-align: center; }
        h2 { text-align: center; margin-bottom: 4px; }
        p.meta { text-align: center; color: #555; margin-top: 0; font-size: 10px; }
    </style>
</head>
<body>
    <h2>Laporan Stok Opname</h2>
    <p class="meta">
        Periode: {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}<br>
        Dicetak: {{ date('d M Y H:i') }} · Hanya data berstatus Disetujui
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->book->book_code ?? '-' }}</td>
                    <td>{{ $row->book->title ?? '-' }}</td>
                    <td class="text-center">{{ $row->stok_fisik }}</td>
                    <td class="text-center">{{ $row->stok_dipinjam }}</td>
                    <td class="text-center">{{ $row->jumlah_hilang_ditemukan ?? 0 }}</td>
                    <td class="text-center">{{ $row->jumlah_rusak_ringan ?? 0 }}</td>
                    <td class="text-center">{{ $row->jumlah_rusak_sedang ?? 0 }}</td>
                    <td class="text-center">{{ $row->jumlah_rusak_berat ?? 0 }}</td>
                    <td class="text-center">{{ $row->selisihLabel() }}</td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                    <td>{{ $row->validator->name ?? '-' }}</td>
                    <td class="text-center">{{ $row->validated_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) + 1 }}" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
