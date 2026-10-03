<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    private $rowNumber = 0;

    // Kita terima data dari Controller lewat constructor
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    // Menentukan Judul Kolom di Excel
    public function headings(): array
    {
        return [
            'No.',
            'Nama Anggota',
            'NIS',
            'Keterangan',
            'Total Denda',
            'Status',
            'Tanggal Kembali'
        ];
    }

    // Menentukan data apa yang masuk ke setiap kolom
    public function map($borrowing): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $borrowing->student->name ?? 'N/A',
            $borrowing->student->nis ?? '-',
            'Keterlambatan Pengembalian',
            $borrowing->fine->total_fine ?? 0,
            ($borrowing->fine->fine_status == 'paid') ? 'Dibayar' : 'Belum Dibayar',
            $borrowing->return_date,
        ];
    }
}