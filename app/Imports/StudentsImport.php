<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Men-generate beberapa default data untuk kolom wajib (Required) di Database Anda
        $kelas = $row['kelas'] ?? null;

        return new Student([
            'nis'           => $row['nis'],
            'name'          => $row['nama_siswa'],
            'class'         => $kelas,
            'major'         => $row['jurusan'] ?? null,
            'phone'         => $row['no_telepon'],
            'status'        => isset($row['status']) && strtolower($row['status']) === 'aktif' ? 'active' : 'inactive',
            'pob'           => '-',
            'dob'           => '2000-01-01',
            'gender'        => 'Laki-laki',
            'address'       => '-',
            'password'      => bcrypt($row['nis']),
        ]);
    }

    public function rules(): array
    {
        return [
            'nis'         => 'required|digits_between:1,7|unique:students,nis',
            'nama_siswa'  => 'required|string|max:255',
            'kelas'       => 'required|in:X,XI,XII',
            'no_telepon'  => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nis.required'         => 'NIS tidak boleh kosong pada file Excel.',
            'nis.digits_between' => 'NIS maksimal 7 digit angka.',
            'nama_siswa.required'  => 'Nama Siswa tidak boleh kosong pada file Excel.',
            'kelas.required'       => 'Kelas tidak boleh kosong pada file Excel.',
            'kelas.in'             => 'Kelas pada Excel harus X, XI, atau XII.',
            'no_telepon.required'  => 'Nomor Telepon wajib diisi.',
            'no_telepon.numeric'   => 'Nomor Telepon harus berupa angka.',
        ];
    }
}