<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penambahan Stok</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        h2, p { text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Penambahan Stok</h2>
    <p>Periode: {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }} · Hanya data Disetujui</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                @foreach($headers as $header)<th>{{ $header }}</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->book->book_code ?? '-' }}</td>
                    <td>{{ $row->book->title ?? '-' }}</td>
                    <td class="text-center">{{ $row->stok_sebelum }}</td>
                    <td class="text-center">{{ $row->jumlah_ditambah }}</td>
                    <td class="text-center">{{ $row->stok_sesudah }}</td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                    <td>{{ $row->validator->name ?? '-' }}</td>
                    <td class="text-center">{{ $row->validated_at?->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ count($headers)+1 }}" class="text-center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
