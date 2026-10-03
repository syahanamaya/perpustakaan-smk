<x-layouts.app title="Tambah Pengumuman - Kepala Perpustakaan">
    <div class="max-w-3xl mx-auto px-6 py-6 space-y-6">
        <div>
            <a href="{{ route('head.announcements.index') }}" class="text-xs font-semibold text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar</a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-150 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide border-b border-gray-50 pb-3">Buat Pengumuman Baru</h3>

            <form action="{{ route('head.announcements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-gray-600 mb-1">Judul Pengumuman</label>
                    <input type="text" name="title" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-600 mb-1">Kategori / Tipe</label>
                        <select name="type" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="info">Info</option>
                            <option value="penting">Penting</option>
                            <option value="kegiatan">Kegiatan</option>
                            <option value="pengingat">Pengingat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-600 mb-1">Upload Flyer / Gambar Banner (Opsional)</label>
                        <input type="file" name="image" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-gray-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-600 mb-1">Isi Konten Pengumuman</label>
                    <textarea name="content" rows="6" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <a href="{{ route('head.announcements.index') }}" class="px-5 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-all">Batal</a>
                    <button type="submit" name="action" value="draft" class="px-5 py-2 bg-gray-200 text-gray-800 font-bold rounded-xl hover:bg-gray-300 shadow-sm transition-all">Simpan Draft</button>
                    <button type="submit" name="action" value="publish" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-sm transition-all">Terbitkan Informasi</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>