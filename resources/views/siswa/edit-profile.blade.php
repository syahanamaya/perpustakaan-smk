<x-layouts.app title="Edit Profil Saya">
    <div class="bg-[#1e56a0] pb-20 pt-5 px-6">
        <div class="max-w-3xl mx-auto">
            <a href="{{ route('student.dashboard') }}" class="text-white/80 hover:text-white text-sm flex items-center gap-2 mb-6 transition-all">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="max-w-3xl mx-auto -mt-20 px-6 pb-12">
        <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            @csrf
            @method('PUT')
            <h1 class="px-6 pt-3 text-3xl font-medium text-[#1e56a0]">Pengaturan Profil</h1>
            <div class="p-8">
                <div class="flex flex-col md:flex-row gap-8 items-start">
                    <!-- Foto Profil -->
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <div class="relative group">
                            <div class="w-32 h-32 rounded-2xl overflow-hidden border-4 border-gray-50 shadow-md">
                                <img id="preview" src="{{ $student->photo ? asset('storage/' . $student->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name) }}" class="w-full h-full object-cover">
                            </div>
                            <label for="photo" class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center cursor-pointer shadow-lg hover:bg-blue-700 transition-all">
                                <i class="fas fa-camera text-sm"></i>
                                <input type="file" name="photo" id="photo" class="hidden" onchange="previewImage(event)">
                            </label>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-4 text-center italic">Format: JPG, PNG. Maksimal 2MB</p>
                    </div>

                    <!-- Input Data -->
                    <div class="w-full md:w-2/3 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Induk Siswa (NIS)</label>
                                <input type="text" value="{{ $student->nis }}" class="w-full bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 text-gray-500 text-sm focus:outline-none cursor-not-allowed" readonly>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ $student->name }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-gray-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</label>
                                <input type="text" value="{{ $student->class }}" class="w-full bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 text-gray-500 text-sm focus:outline-none cursor-not-allowed" readonly>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Jurusan</label>
                                <input type="text" value="{{ $student->major }}" class="w-full bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 text-gray-500 text-sm focus:outline-none cursor-not-allowed" readonly>
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ganti Password (Opsional)</label>
                            <input type="password" name="password" placeholder="Isi hanya jika ingin mengganti password" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-gray-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-5 flex justify-end gap-3">
                <button type="reset" class="px-6 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 transition-all">Batal</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl text-sm font-medium shadow-lg shadow-blue-200 transition-all transform active:scale-95">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('preview');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</x-layouts.app>