<?php

namespace App\Http\Controllers\Admin\Head;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * Menampilkan daftar pengumuman dan statistik.
     */
    public function index(Request $request)
    {
        // Ambil semua pengumuman diurutkan berdasarkan yang terbaru
        $query = Announcement::query()->with('creator')->latest();
        
        $announcements = $query->paginate(10);

        // Kirim data ke view
        return view('admin.head.announcements.index', compact(
            'announcements',
        ));
    }

    /**
     * Menyimpan pengumuman baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,penting,kegiatan,pengingat',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->storeAs('public/announcements', $imageName);
        }

        // Tentukan status berdasarkan tombol yang diklik
        $publishedAt = null;
        if ($request->input('action') === 'publish') {
            $publishedAt = now();
        }

        Announcement::create([
            'title' => $request->input('title'),
            'slug' => Str::slug($request->input('title')) . '-' . uniqid(),
            'content' => $request->input('content'),
            'type' => $request->input('type'),
            'image' => $imageName,
            'user_id' => Auth::id(),
            'published_at' => $publishedAt,
        ]);

        $message = $publishedAt ? 'Pengumuman berhasil dibuat dan diterbitkan.' : 'Pengumuman berhasil disimpan sebagai draft.';
        return redirect()->route('head.announcements.index')->with('success', $message);
    }

    /**
     * Memperbarui data pengumuman yang sudah ada.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,penting,kegiatan,pengingat',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $data = $request->only(['title', 'content', 'type']);
        $data['slug'] = Str::slug($request->input('title')) . '-' . $announcement->id;
    
        if ($request->hasFile('image')) {
            if ($announcement->image && Storage::disk('public')->exists('announcements/' . $announcement->image)) {
                Storage::disk('public')->delete('announcements/' . $announcement->image);
            }
            $imageName = time().'.'.$request->image->extension();
            $request->image->storeAs('public/announcements', $imageName);
            $data['image'] = $imageName;
        }
    
        // Handle status penerbitan berdasarkan tombol yang ditekan
        if ($request->has('action')) {
            if ($request->input('action') === 'publish') {
                $data['published_at'] = now();
            } else { // 'draft'
                $data['published_at'] = null;
            }
        }
    
        $announcement->update($data);
    
        return redirect()->route('head.announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }


    /**
     * Menghapus pengumuman.
     */
    public function destroy(Announcement $announcement)
    {
        // Hapus gambar yang terhubung dengan pengumuman jika ada
        if ($announcement->image && Storage::disk('public')->exists('announcements/' . $announcement->image)) {
            Storage::disk('public')->delete('announcements/' . $announcement->image);
        }

        $announcement->destroy($announcement->id);
        return redirect()->route('head.announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * Memperbarui status pengumuman (misalnya: menjadi draft, terjadwal, atau terbit).
     */
    public function updateStatus(Request $request, Announcement $announcement)
    {
        $request->validate([
            'status' => 'required|in:draft,scheduled,published',
            'published_at' => 'nullable|date|required_if:status,scheduled',
        ], [
            'published_at.required_if' => 'Tanggal terbit wajib diisi jika statusnya dijadwalkan.'
        ]);

        $status = $request->status;
        $publishedAt = $announcement->published_at;
        $message = '';

        switch ($status) {
            case 'draft':
                $publishedAt = null;
                $message = 'Pengumuman berhasil diubah menjadi draft.';
                break;
            case 'scheduled':
                $publishedAt = $request->published_at;
                $message = 'Pengumuman berhasil dijadwalkan.';
                break;
            case 'published':
                $publishedAt = now();
                $message = 'Pengumuman berhasil diterbitkan.';
                break;
        }

        $announcement->update([
            'published_at' => $publishedAt,
        ]);

        return redirect()->back()->with('success', $message);
    }
}