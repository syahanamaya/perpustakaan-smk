<x-layouts.app title="Edit Pengumuman - Kepala Perpustakaan">
    <div class="max-w-3xl mx-auto px-6 py-6 space-y-6">
        <div>
            <a href="{{ route('head.announcements.index') }}" class="text-xs font-semibold text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar</a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-150 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide border-b border-gray-50 pb-3">Edit Pengumuman</h3>

            <form action="{{ route('head.announcements.update', $announcement->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-gray-600 mb-1">Judul Pengumuman</label>
                    <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-600 mb-1">Kategori / Tipe</label>
                        <select name="type" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="info" {{ old('type', $announcement->type) == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="penting" {{ old('type', $announcement->type) == 'penting' ? 'selected' : '' }}>Penting</option>
                            <option value="kegiatan" {{ old('type', $announcement->type) == 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                            <option value="pengingat" {{ old('type', $announcement->type) == 'pengingat' ? 'selected' : '' }}>Pengingat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-600 mb-1">Update Flyer / Gambar (Opsional)</label>
                        <input type="file" name="image" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-gray-500">
                        @if($announcement->image)
                            <div class="mt-2 flex items-center gap-2 text-[10px] text-gray-500 font-medium">
                                <i class="fas fa-image text-blue-500"></i>
                                <span>Gambar saat ini: <a href="{{ asset('storage/announcements/'.$announcement->image) }}" target="_blank" class="text-blue-600 hover:underline">Lihat Gambar</a></span>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-600 mb-1">Isi Konten Pengumuman</label>
                    <textarea name="content" rows="6" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('content', $announcement->content) }}</textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <a href="{{ route('head.announcements.index') }}" class="px-5 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-all">Batal</a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-sm transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>