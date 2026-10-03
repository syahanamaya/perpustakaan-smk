<x-layouts.app>
    <div class="p-6">
        {{-- Header & Back Button --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Rak Baru</h1>
                <p class="text-sm text-gray-500">Masukkan data rak untuk menentukan lokasi buku</p>
            </div>
            <a href="{{ route('raks.index') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 font-medium transition-all">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Daftar
            </a>
        </div>

        {{-- Form Card --}}
        <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('raks.store') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    {{-- Input Nama Rak --}}
                    <div>
                        <label for="nama_rak" class="block text-sm font-bold text-gray-700 mb-2">Kode Rak</label>
                        <input type="text" name="nama_rak" id="nama_rak" 
                               class="w-full px-4 py-3 rounded-xl border{{ $errors->has('nama_rak') ? ' border-red-500' : ' border-gray-200' }} focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                               placeholder="Contoh: FIKSI01" value="{{ old('nama_rak') }}" required>
                        @error('nama_rak')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Lokasi --}}
                    <div>
                        <label for="lokasi" class="block text-sm font-bold text-gray-700 mb-2">Lokasi / Keterangan</label>
                        <input type="text" name="lokasi" id="lokasi" 
                               class="w-full px-4 py-3 rounded-xl border {{ $errors->has('lokasi') ? 'border-red-500' : 'border-gray-200' }} focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                               placeholder="Contoh: Lantai 1 - Area Timur" value="{{ old('lokasi') }}" required>
                        @error('lokasi')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Action Button --}}
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition-all flex justify-center items-center gap-2">
                            <i class="fas fa-save"></i>
                            Simpan Data Rak
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>