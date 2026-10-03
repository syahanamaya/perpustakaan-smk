<x-layouts.app title="Edit Data Siswa - Manajemen Perpustakaan">
    <div class="max-w-4xl mx-auto space-y-4">
        
        <!-- FORM CARD -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data" class="p-6" novalidate>
                @csrf
                @method('PUT')

                <!-- SECTION 1: Data Identitas Utama -->
                <div class="mb-6">
                    <h2 class="text-[#2563EB] font-semibold flex items-center gap-2 mb-4 text-[12px] border-b border-gray-100 pb-2 uppercase tracking-wide">
                        <i class="fas fa-id-card text-sm"></i> Data Identitas Utama
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
                        <!-- NIS -->
                        <div>
                            <label for="nis" class="block text-[11px] font-medium text-gray-600 mb-1.5">NIS <span class="text-red-500">*</span></label>
                            <input type="text" id="nis" name="nis" value="{{ old('nis', $student->nis) }}" required maxlength="7" inputmode="numeric" pattern="[0-9]{1,7}" placeholder="Maksimal 7 digit"
                                   class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors outline-none">
                            <p class="text-[9px] text-gray-400 mt-1">Hanya angka, maksimal 7 digit.</p>
                            @error('nis') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-[11px] font-medium text-gray-600 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $student->name) }}" required placeholder="Sesuai buku induk sekolah"
                                   class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors outline-none">
                            @error('name') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Kelas -->
                        <div>
                            <label for="class" class="block text-[11px] font-medium text-gray-600 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                            <select id="class" name="class" required class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors outline-none">
                                <option value="">-- Pilih Kelas --</option>
                                <option value="X" @selected(old('class', $student->class) === 'X')>Kelas X</option>
                                <option value="XI" @selected(old('class', $student->class) === 'XI')>Kelas XI</option>
                                <option value="XII" @selected(old('class', $student->class) === 'XII')>Kelas XII</option>
                            </select>
                            @error('class') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Jurusan -->
                        <div>
                            <label for="major" class="block text-[11px] font-medium text-gray-600 mb-1.5">Jurusan</label>
                            <select id="major" name="major" class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors outline-none">
                                <option value="">-- Pilih Jurusan --</option>
                                <option value="RPL" @selected(old('major', $student->major) === 'RPL')>RPL</option>
                                <option value="TKJ" @selected(old('major', $student->major) === 'TKJ')>TKJ</option>
                                <option value="AKL" @selected(old('major', $student->major) === 'AKL')>AKL</option>
                            </select>
                            @error('major') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Data Pendukung (Profil) -->
                <div class="mb-6">
                    <h2 class="text-[#2563EB] font-semibold flex items-center gap-2 mb-4 text-[12px] border-b border-gray-100 pb-2 uppercase tracking-wide">
                        <i class="fas fa-user-circle text-sm"></i> Data Pendukung (Profil)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
                        <!-- Tempat & Tanggal Lahir -->
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label for="pob" class="block text-[11px] font-medium text-gray-600 mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
                                <input type="text" id="pob" name="pob" value="{{ old('pob', $student->pob) }}" required placeholder="Contoh: Jakarta"
                                       class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white">
                                @error('pob') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="w-1/2">
                                <label for="dob" class="block text-[11px] font-medium text-gray-600 mb-1.5">Tgl Lahir <span class="text-red-500">*</span></label>
                                <input type="date" id="dob" name="dob" value="{{ old('dob', $student->dob ? $student->dob->format('Y-m-d') : '') }}" required 
                                       class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white">
                                @error('dob') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block text-[11px] font-medium text-gray-600 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-1.5 text-[11px] text-gray-600 cursor-pointer">
                                    <input type="radio" name="gender" value="Laki-laki" class="text-blue-600 focus:ring-blue-500" @checked(old('gender', $student->gender) === 'Laki-laki')> Laki-laki
                                </label>
                                <label class="flex items-center gap-1.5 text-[11px] text-gray-600 cursor-pointer">
                                    <input type="radio" name="gender" value="Perempuan" class="text-blue-600 focus:ring-blue-500" @checked(old('gender', $student->gender) === 'Perempuan')> Perempuan
                                </label>
                            </div>
                            @error('gender') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- No Telepon -->
                        <div>
                            <label for="phone" class="block text-[11px] font-medium text-gray-600 mb-1.5">No. Telepon/HP <span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $student->phone) }}" required placeholder="Contoh: 08123456789"
                                   class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 bg-gray-50 focus:bg-white outline-none">
                            @error('phone') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Foto -->
                        <div>
                            <label for="photo" class="block text-[11px] font-medium text-gray-600 mb-1.5">Foto Profil Baru (Opsional)</label>
                            <div class="flex items-center gap-3">
                                @if($student->photo)
                                    <img src="{{ asset('storage/' . $student->photo) }}" alt="Current Photo" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-sm">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center border border-gray-200 text-gray-400">
                                        <i class="fas fa-user text-[10px]"></i>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <input type="file" id="photo" name="photo" accept="image/png, image/jpeg, image/jpg"
                                           class="w-full text-[11px] text-gray-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 outline-none">
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-400 mt-1">Biarkan kosong jika tidak ingin mengubah foto.</p>
                            @error('photo') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Alamat -->
                        <div class="md:col-span-2">
                            <label for="address" class="block text-[11px] font-medium text-gray-600 mb-1.5">Alamat Lengkap <span class="text-red-500">*</span></label>
                            <textarea id="address" name="address" rows="2" required placeholder="Masukkan alamat lengkap"
                                      class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 bg-gray-50 focus:bg-white outline-none resize-none">{{ old('address', $student->address) }}</textarea>
                            @error('address') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-[11px] font-medium text-gray-600 mb-1.5">Status <span class="text-red-500">*</span></label>
                            <select id="status" name="status" class="w-full border border-gray-200 rounded-md text-[11px] py-1.5 px-3 focus:ring-1 focus:ring-blue-500 bg-gray-50 focus:bg-white outline-none">
                                <option value="active" @selected(old('status', $student->status) === 'active')>Aktif</option>
                                <option value="inactive" @selected(old('status', $student->status) === 'inactive')>Tidak Aktif</option>
                                <option value="graduated" @selected(old('status', $student->status) === 'graduated')>Lulus</option>
                            </select>
                            @error('status') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- FOOTER ACTIONS -->
                <div class="pt-4 mt-6 border-t border-gray-100 flex justify-end gap-2">
                    <a href="{{ route('admin.students.index') }}" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-md text-[11px] font-medium transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-1.5 bg-[#2563EB] hover:bg-blue-700 text-white rounded-md text-[11px] font-medium shadow-sm transition-colors flex items-center gap-1.5">
                        <i class="fas fa-save text-[10px]"></i> Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>