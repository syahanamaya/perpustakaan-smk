<x-layouts.app title="Kelola Pengumuman - Kepala Perpustakaan">
    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .bg-grid-slate-100 {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='%23f1f5f9'%3E%3Cpath d='M0 .5H31.5V32'/%3E%3C/svg%3E");
        }
        [x-cloak] { display: none !important; }
    </style>
    @endsection

    <div x-data='{
        showCreateModal: {{ $errors->any() && !old("announcement_id") ? "true" : "false" }},
        showEditModal: {{ $errors->any() && old("announcement_id") ? "true" : "false" }},
        showPreviewModal: false,
        previewAnnouncement: {},
        editAnnouncement: {},
        init() {
            if (this.showEditModal) {
                let announcementId = {{ old("announcement_id", "null") }};
                if (announcementId) {
                    const allAnnouncements = @json($announcements->items());
                    const announcementForEdit = allAnnouncements.find(a => a.id == announcementId);
                    if (announcementForEdit) {
                        this.editAnnouncement = {
                            id: announcementForEdit.id,
                            title: @json(old("title")) || announcementForEdit.title,
                            content: @json(old("content")) || announcementForEdit.content,
                            type: @json(old("type")) || announcementForEdit.type
                        };
                    }
                }
            }
        },
        openEditModal(announcement) {
            this.editAnnouncement = JSON.parse(JSON.stringify(announcement));
            this.showEditModal = true;
        },
        openPreviewModal(announcement) {
            this.previewAnnouncement = JSON.parse(JSON.stringify(announcement));
            this.showPreviewModal = true;
        }
    }' class="max-w-7xl mx-auto px-4 py-6 space-y-4">

        {{-- NOTIFIKASI SUKSES --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-medium flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[9px]">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        @endif

        {{-- NOTIFIKASI ERROR --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-100 text-red-800 px-4 py-3 rounded-2xl text-xs font-medium shadow-sm">
                <p class="font-medium mb-1">Gagal menyimpan data:</p>
                <ul class="list-disc pl-4 space-y-0.5 text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- AREA TABEL DATA --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white">
                <h2 class="text-xs font-medium text-gray-800 tracking-tight flex items-center gap-2">
                    Daftar Pengumuman 
                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2.5 py-0.5 rounded-full font-medium">{{ $announcements->total() }} data</span>
                </h2>
                <button @click="showCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-medium shadow-sm transition-all hover:-translate-y-0.5">
                    <i class="fas fa-plus text-[10px]"></i> Tambah Pengumuman
                </button>
            </div>
            
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/40 text-gray-400 uppercase tracking-wider font-medium text-[10px]">
                            <th class="px-5 py-3 w-12 text-center">No</th>
                            <th class="px-5 py-3">Informasi Pengumuman</th>
                            <th class="px-5 py-3 text-center">Kategori</th>
                            <th class="px-5 py-3 text-center">Penulis</th>
                            <th class="px-5 py-3 text-center">Tanggal Rilis</th>
                            <th class="px-5 py-3 text-center w-14">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-600">
                        @forelse($announcements as $index => $announ)
                            <tr class="transition-colors group {{ $announ->type === 'penting' ? 'bg-blue-50/20' : 'hover:bg-gray-50/50 bg-white' }}">
                                <td class="px-5 py-4 text-center font-medium text-gray-400">
                                    @if($announ->type === 'penting' && $announcements->currentPage() == 1)
                                        <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto shadow-sm" title="Penting / Disematkan">
                                            <i class="fas fa-thumbtack transform rotate-45 text-[9px]"></i>
                                        </div>
                                    @else
                                        <span class="group-hover:text-gray-600 transition-colors">{{ $announcements->firstItem() + $index }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0 flex items-center justify-center shadow-inner relative">
                                            @if($announ->image)
                                                <img src="{{ asset('storage/announcements/' . $announ->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            @else
                                                <i class="fas fa-bullhorn text-gray-300 text-base"></i>
                                            @endif
                                        </div>
                                        <div class="max-w-70 whitespace-normal">
                                            <h4 class="font-medium text-gray-800 text-xs leading-snug group-hover:text-blue-600 transition-colors line-clamp-1 mb-0.5">
                                                {{ $announ->title }}
                                            </h4>
                                            <p class="text-gray-400 text-[11px] leading-relaxed line-clamp-2">
                                                {{ Str::limit(strip_tags($announ->content), 80) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($announ->type === 'penting')
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-red-50 text-red-600 border border-red-100 text-[10px] font-medium rounded-full uppercase tracking-wider">Penting</span>
                                    @elseif($announ->type === 'kegiatan')
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-purple-50 text-purple-600 border border-purple-100 text-[10px] font-medium rounded-full uppercase tracking-wider">Kegiatan</span>
                                    @elseif($announ->type === 'pengingat')
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 text-[10px] font-medium rounded-full uppercase tracking-wider">Pengingat</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-medium rounded-full uppercase tracking-wider">Informasi</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-linear-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-[10px] font-medium shadow-sm">
                                            {{ strtoupper(substr($announ->creator->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-gray-700">{{ $announ->creator->name ?? 'Kepala Perpustakaan' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($announ->created_at)->format('d M Y') }}</span>
                                        <span class="text-gray-400 text-[10px] flex items-center gap-1 mt-0.5">
                                            <i class="far fa-clock text-[9px]"></i> {{ \Carbon\Carbon::parse($announ->created_at)->format('H:i') }} WIB
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-center gap-1">
                                        <button @click='openPreviewModal(@json($announ))' class="p-1.5 text-blue-400 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Pratinjau"><i class="fas fa-eye"></i></button>
                                        <button @click='openEditModal(@json($announ))' class="p-1.5 text-orange-400 hover:bg-orange-50 rounded-lg transition-colors" title="Edit"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('head.announcements.destroy', $announ->id) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition-colors" onclick="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center bg-gray-50/10">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mb-3 border border-gray-100">
                                            <i class="fas fa-bullhorn text-lg text-gray-300"></i>
                                        </div>
                                        <p class="font-medium text-gray-700 text-xs">Belum ada pengumuman</p>
                                        <p class="text-gray-400 text-[11px] mt-1 leading-relaxed">Klik tombol "Tambah Pengumuman" untuk membuat informasi rilis baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($announcements->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/20 flex justify-center">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>

        {{-- MODAL CREATE --}}
        <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showCreateModal = false" x-show="showCreateModal" x-transition.opacity></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-white">
                        <h3 class="text-sm font-medium text-gray-800 flex items-center gap-2"><i class="fas fa-plus-circle text-blue-600"></i> Tambah Pengumuman</h3>
                        <button @click="showCreateModal = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times"></i></button>
                    </div>

                    <form action="{{ route('head.announcements.store') }}" method="POST" class="p-5 space-y-4 text-xs" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label class="text-gray-600 mb-1.5 block font-medium">Judul Pengumuman <span class="text-red-400">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="Tulis judul di sini..." required class="w-full p-2.5 border border-gray-200 rounded-xl outline-none focus:border-blue-500 text-gray-700 transition-all">
                        </div>

                        <div>
                            <label class="text-gray-600 mb-1.5 block font-medium">Isi Konten <span class="text-red-400">*</span></label>
                            <textarea name="content" placeholder="Tulis isi pengumuman..." required rows="5" class="w-full p-2.5 border border-gray-200 rounded-xl outline-none focus:border-blue-500 text-gray-700 transition-all">{{ old('content') }}</textarea>
                        </div>

                        <div>
                            <label class="text-gray-600 mb-1.5 block font-medium">Kategori <span class="text-red-400">*</span></label>
                            <select name="type" class="w-full p-2.5 border border-gray-200 rounded-xl bg-white outline-none focus:border-blue-500 text-gray-700 cursor-pointer">
                                <option value="info" @selected(!old('announcement_id') && old('type') == 'info')>Informasi</option>
                                <option value="penting" @selected(!old('announcement_id') && old('type') == 'penting')>Penting</option>
                                <option value="kegiatan" @selected(!old('announcement_id') && old('type') == 'kegiatan')>Kegiatan</option>
                                <option value="pengingat" @selected(!old('announcement_id') && old('type') == 'pengingat')>Pengingat</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-gray-600 mb-1.5 block font-medium">Gambar Banner (Opsional)</label>
                            <input type="file" name="image" class="w-full text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-1.5">Format: JPG, PNG, GIF. Maks: 2MB.</p>
                        </div>

                        <div class="pt-3 flex justify-end gap-2 border-t border-gray-100 bg-gray-50/50 -mx-5 -mb-5 p-4 rounded-b-2xl">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 font-medium text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">Batal</button>
                            <button type="submit" name="action" value="publish" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl font-medium transition-colors flex items-center gap-1.5 shadow-sm"><i class="far fa-paper-plane"></i> Terbitkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT --}}
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showEditModal = false" x-show="showEditModal" x-transition.opacity></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-white">
                        <h3 class="text-sm font-medium text-gray-800 flex items-center gap-2"><i class="fas fa-edit text-orange-500"></i> Edit Pengumuman</h3>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times"></i></button>
                    </div>

                    <form :action="'/head/announcements/' + editAnnouncement.id" method="POST" class="p-5 space-y-4 text-xs" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="announcement_id" :value="editAnnouncement.id">

                        <div>
                            <label class="text-gray-600 mb-1.5 block font-medium">Judul Pengumuman <span class="text-red-400">*</span></label>
                            <input type="text" name="title" x-model="editAnnouncement.title" placeholder="Tulis judul di sini..." required class="w-full p-2.5 border border-gray-200 rounded-xl outline-none focus:border-blue-500 text-gray-700 transition-all">
                        </div>

                        <div>
                            <label class="text-gray-600 mb-1.5 block font-medium">Isi Konten <span class="text-red-400">*</span></label>
                            <textarea name="content" x-model="editAnnouncement.content" placeholder="Tulis isi pengumuman..." required rows="5" class="w-full p-2.5 border border-gray-200 rounded-xl outline-none focus:border-blue-500 text-gray-700 transition-all"></textarea>
                        </div>

                        <div>
                            <label class="text-gray-600 mb-1.5 block font-medium">Kategori <span class="text-red-400">*</span></label>
                            <select name="type" x-model="editAnnouncement.type" class="w-full p-2.5 border border-gray-200 rounded-xl bg-white outline-none focus:border-blue-500 text-gray-700 cursor-pointer">
                                <option value="info">Informasi</option>
                                <option value="penting">Penting</option>
                                <option value="kegiatan">Kegiatan</option>
                                <option value="pengingat">Pengingat</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-gray-600 mb-1.5 block font-medium">Gambar Banner (Opsional)</label>
                            <input type="file" name="image" class="w-full text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-1.5">Format: JPG, PNG, GIF. Maks: 2MB. Mengunggah gambar baru akan menggantikan yang lama.</p>
                        </div>

                        <div class="pt-3 flex justify-end gap-2 border-t border-gray-100 bg-gray-50/50 -mx-5 -mb-5 p-4 rounded-b-2xl">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 font-medium text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">Batal</button>
                            <button type="submit" name="action" value="publish" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl font-medium transition-colors flex items-center gap-1.5 shadow-sm"><i class="far fa-paper-plane"></i> Terbitkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL PREVIEW --}}
        <div x-show="showPreviewModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showPreviewModal = false" x-show="showPreviewModal" x-transition.opacity></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                     x-show="showPreviewModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <template x-if="previewAnnouncement.id">
                        <div>
                            <div class="w-full h-48 bg-gray-200" :class="{'hidden': !previewAnnouncement.image}">
                                <img :src="'/storage/announcements/' + previewAnnouncement.image" alt="Banner" class="w-full h-full object-cover">
                            </div>

                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900" x-text="previewAnnouncement.title"></h3>
                                        <div class="flex items-center gap-4 text-xs text-gray-500 mt-2">
                                            <div class="flex items-center gap-1.5">
                                                <i class="fas fa-user-circle"></i>
                                                <span x-text="previewAnnouncement.creator ? previewAnnouncement.creator.name : 'Kepala Perpustakaan'"></span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span x-text="new Date(previewAnnouncement.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 text-[10px] font-medium rounded-full tracking-wider capitalize" x-text="previewAnnouncement.type"></span>
                                </div>

                                <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed" x-html="previewAnnouncement.content.replace(/\n/g, '<br>')">
                                </div>
                            </div>

                            <div class="px-6 py-3 bg-gray-50/50 flex justify-end border-t border-gray-100 rounded-b-2xl">
                                <button type="button" @click="showPreviewModal = false" class="px-4 py-2 font-medium text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">Tutup</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>