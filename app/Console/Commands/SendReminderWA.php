<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Borrowing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendReminderWA extends Command
{
    protected $signature = 'reminder:wa';
    protected $description = 'Kirim notifikasi WhatsApp H-1 sebelum batas pengembalian buku';

    public function handle()
    {
        $this->info('Memulai pengecekan jadwal pengembalian buku...');

        $besok = Carbon::tomorrow()->startOfDay();

        $peminjaman = Borrowing::with(['student', 'details.book'])
            ->where('status', 'borrowed')
            ->whereDate('due_date', $besok)
            ->get();

        if ($peminjaman->isEmpty()) {
            $this->info('Tidak ada jadwal pengembalian buku untuk besok.');
            return;
        }

        $berhasil = 0;
        $gagal = 0;

        foreach ($peminjaman as $trx) {
            $siswa = $trx->student;

            if (!$siswa || empty($siswa->phone)) {
                continue;
            }

            $noWa = $siswa->phone;
            
            $judulBuku = $trx->details->map(function ($detail) {
                return $detail->book->title ?? 'Buku Tidak Diketahui';
            })->implode(', ');

            $tanggalKembali = Carbon::parse($trx->due_date)->format('d M Y');

            $pesan = "Halo {$siswa->name},\n\nMengingatkan bahwa buku *{$judulBuku}* yang Anda pinjam harus dikembalikan besok pada tanggal *{$tanggalKembali}*. Jangan sampai terlambat ya agar tidak terkena denda!\n\nTerima kasih,\nPerpustakaan SMK Budi Mulia";

            try {
                $response = Http::timeout(30) // Perpanjang batas waktu tunggu menjadi 30 detik
                    ->retry(3, 2000) // Ulangi kirim max 3 kali dengan jeda 2 detik jika gagal/timeout
                    ->withHeaders([
                        'Authorization' => env('FONNTE_TOKEN', 'TOKEN_ANDA_DISINI'),
                    ])->post('https://api.fonnte.com/send', [
                        'target' => $noWa,
                        'message' => $pesan,
                        'countryCode' => '62',
                    ]);

                if ($response->successful()) {
                    Log::info("WA Reminder terkirim ke {$siswa->name} ({$noWa})");
                    $berhasil++;
                } else {
                    Log::error("Gagal kirim WA ke {$siswa->name}: " . $response->body());
                    $gagal++;
                }
            } catch (\Exception $e) {
                Log::error("Error API Fonnte ke {$siswa->name}: " . $e->getMessage());
                $gagal++;
            }
        }

        $this->info("Pengiriman selesai. Berhasil: {$berhasil}, Gagal: {$gagal}.");
    }
}
