<?php

namespace App\Http\Controllers\Admin\Head;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // 1. STATISTIK RINGKASAN (Data Asli)
        $total_siswa = Student::count();
        $siswa_aktif = Student::where('status', 'Aktif')->count();
        $siswa_nonaktif = Student::where('status', 'Nonaktif')->count();
        $total_kelas = Student::distinct('class')->count('class');

        // 2. QUERY DAFTAR SISWA DENGAN FILTER
        $query = Student::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
        }

        if ($request->class && $request->class != 'Semua Kelas') {
            $query->where('class', $request->class);
        }

        $students = $query->paginate(10);

        // 3. DATA UNTUK GRAFIK (Pie Chart Kategori Kelas)
        $students_per_class = Student::select('class', DB::raw('count(*) as total'))
            ->groupBy('class')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 4. DATA JENIS KELAMIN
        $laki_laki = Student::where('gender', 'Laki-laki')->count();
        $perempuan = Student::where('gender', 'Perempuan')->count();

        // Ambil daftar kelas unik untuk dropdown filter
        $list_kelas = Student::distinct()->pluck('class');

        return view('admin.head.students.index', compact(
            'total_siswa', 'siswa_aktif', 'siswa_nonaktif', 'total_kelas',
            'students', 'students_per_class', 'laki_laki', 'perempuan', 'list_kelas'
        ));
    }
}