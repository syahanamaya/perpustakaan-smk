<?php

namespace App\Http\Controllers\Admin\Head;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ClassLoanQuota;
use Illuminate\Http\Request;

class ClassLoanRuleController extends Controller
{
    public function index()
    {
        $rules = ClassLoanQuota::forHeadForm();
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.head.loan-rules.index', compact('rules', 'categories'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rules' => 'required|array',
            'rules.*.class_level' => 'required|in:X,XI,XII',
            'rules.*.max_paket' => 'required|integer|min:0|max:20',
            'rules.*.max_bebas' => 'required|integer|min:0|max:20',
        ], [
            'rules.*.max_paket.required' => 'Batas buku paket wajib diisi.',
            'rules.*.max_bebas.required' => 'Batas buku bebas wajib diisi.',
        ]);

        $byLevel = [];
        foreach ($validated['rules'] as $row) {
            $byLevel[$row['class_level']] = [
                'max_paket' => $row['max_paket'],
                'max_bebas' => $row['max_bebas'],
            ];
        }
        ClassLoanQuota::save($byLevel);

        return back()->with('success', 'Batas kuota pinjam per kelas berhasil diperbarui.');
    }

    public function updateDurations(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.loan_days' => 'required|integer|min:0|max:400',
            'categories.*.quota_group' => 'required|in:paket,bebas',
        ], [
            'categories.*.loan_days.required' => 'Durasi pinjam kategori wajib diisi.',
            'categories.*.loan_days.min' => 'Durasi tidak boleh negatif. Isi 0 untuk baca di tempat.',
        ]);

        foreach ($validated['categories'] as $row) {
            Category::query()->where('id', $row['id'])->update([
                'loan_days' => $row['loan_days'],
                'quota_group' => $row['quota_group'],
            ]);
        }

        return back()->with('success', 'Durasi pinjam per kategori buku berhasil diperbarui.');
    }
}
