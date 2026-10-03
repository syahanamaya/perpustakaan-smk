<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Peminjaman - {{ $borrowing->transaction_code }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .content {
            margin-bottom: 20px;
        }
        .content h2 {
            font-size: 16px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .info-table, .book-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px 0;
            font-size: 12px;
        }
        .info-table td:first-child {
            width: 150px;
            font-weight: bold;
        }
        .book-table th, .book-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .book-table th {
            background-color: #f7f7f7;
            font-weight: bold;
        }
        .total {
            text-align: right;
            margin-top: 10px;
        }
        .total-item {
            margin-bottom: 5px;
        }
        .total-item span {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 30px;
        }
        .signatures {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signatures > div {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        .signatures .name {
            margin-top: 60px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bukti Transaksi Peminjaman</h1>
            <p>Perpustakaan SMK</p>
        </div>

        <div class="content">
            <h2>Detail Transaksi</h2>
            <table class="info-table">
                <tr>
                    <td>Kode Transaksi</td>
                    <td>: {{ $borrowing->transaction_code }}</td>
                </tr>
                <tr>
                    <td>Tanggal Pinjam</td>
                    <td>: {{ $borrowing->borrow_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Jatuh Tempo</td>
                    <td>: {{ $borrowing->due_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>: {{ $borrowing->status == 'borrowed' ? 'Dipinjam' : 'Dikembalikan' }}</td>
                </tr>
                 @if ($borrowing->status == 'returned')
                <tr>
                    <td>Tanggal Kembali</td>
                    <td>: {{ $borrowing->return_date ? $borrowing->return_date->format('d M Y') : '-' }}</td>
                </tr>
                @endif
            </table>

            <h2>Informasi Peminjam</h2>
            <table class="info-table">
                <tr>
                    <td>Nama Siswa</td>
                    <td>: {{ $borrowing->student->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NIS</td>
                    <td>: {{ $borrowing->student->nis ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>: {{ $borrowing->student->class ?? '-' }}</td>
                </tr>
            </table>

            <h2>Daftar Buku yang Dipinjam</h2>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>ISBN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($borrowing->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->book->title ?? 'Buku tidak ditemukan' }}</td>
                        <td>{{ $detail->book->author ?? '-' }}</td>
                        <td>{{ $detail->book->isbn ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($borrowing->fine && $borrowing->fine->total_fine > 0)
                <h2>Informasi Denda</h2>
                <table class="info-table">
                     <tr>
                        <td>Keterangan</td>
                        <td>: {{ $borrowing->fine->keterangan ?? 'Keterangan tidak tersedia' }}</td>
                    </tr>
                    <tr>
                        <td>Total Denda</td>
                        <td>: Rp {{ number_format($borrowing->fine->total_fine, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Status Denda</td>
                        <td>: {{ $borrowing->fine->fine_status == 'paid' ? 'Lunas' : 'Belum Lunas' }}</td>
                    </tr>
                </table>
            @endif
        </div>

        <div class="signatures">
            <div class="borrower">
                <p>Peminjam,</p>
                <div class="name">({{ $borrowing->student->name ?? '....................' }})</div>
            </div>
            <div class="librarian">
                <p>Petugas Perpustakaan,</p>
                <div class="name">({{ $borrowing->user->name ?? '....................' }})</div>
            </div>
        </div>

        <div class="footer">
            <p>Harap simpan bukti peminjaman ini dengan baik. Bukti ini sah dan dicetak oleh sistem.</p>
            <p>Dicetak pada: {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
