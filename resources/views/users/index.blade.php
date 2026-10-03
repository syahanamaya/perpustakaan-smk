<x-layouts.app title="Manajemen User">
    <div x-data="{ 
        modalOpen: false, 
        isEdit: false, 
        showViewModal: false, // State baru untuk modal detail
        viewUser: {}, // Objek untuk menyimpan data user yang akan dilihat
        formAction: '{{ route('head.users.store') }}', 
        formTitle: 'Tambah User Baru', 
        name: '',
        nip: '',
        email: '',
        phone: '',
        
        resetForm() {
            this.isEdit = false;
            this.formTitle = 'Tambah User Baru';
            this.formAction = '{{ route('head.users.store') }}';
            this.name = '';
            this.nip = '';
            this.email = '';
            this.phone = '';
            document.getElementById('userForm').reset();
        },

        openEditModal(user) {
            this.isEdit = true;
            this.formTitle = 'Edit User: ' + user.name;
            this.formAction = '/head/users/' + user.id;
            this.name = user.name;
            this.nip = user.nip || '';
            this.email = user.email;
            this.phone = user.phone || '';

            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
        },

        openViewModal(user) { // Fungsi baru untuk membuka modal detail
            this.viewUser = JSON.parse(JSON.stringify(user)); // Salin data user
            this.showViewModal = true;
        }
    }" class="bg-gray-50 min-h-screen p-4 text-xs">

        <!-- 2. Filter Bar -->
        <form action="{{ route('head.users.index') }}" method="GET" class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-50">
                <label class="text-gray-500 mb-1 block">Cari User</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau nip..." class="w-full pl-3 pr-10 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 bg-gray-50">
                    <i class="fas fa-search absolute right-3 top-2.5 text-gray-400"></i>
                </div>
            </div>
            <div class="w-48">
                <label class="text-gray-500 mb-1 block">Peran</label>
                <select name="role" class="w-full p-2 border border-gray-200 rounded-lg bg-gray-50 outline-none">
                    <option value="">Semua Peran</option>
                    <option value="head" {{ request('role') == 'head' ? 'selected' : '' }}>Kepala Perpustakaan</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Petugas</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-blue-700 transition">
                <i class="fas fa-filter"></i> Terapkan Filter
            </button>
            <a href="{{ route('head.users.index') }}" class="bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-gray-50">
                <i class="fas fa-sync-alt"></i> Reset
            </a>
        </form>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- 3. Tabel (Kiri) -->
            <div class="lg:w-3/4 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                    <h2 class="text-sm font-medium text-gray-700">Daftar Users <span class="ml-2 text-[10px] bg-blue-50 text-blue-500 px-2 py-0.5 rounded-full">{{ $users->total() }} data</span></h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50/50 text-gray-400 uppercase text-[10px]">
                            <tr>
                                <th class="px-4 py-3 font-normal">No.</th>
                                <th class="px-4 py-3 font-normal">Nama Lengkap</th>
                                <th class="px-4 py-3 font-normal">NIP</th>
                                <th class="px-4 py-3 font-normal">Peran</th>
                                <th class="px-4 py-3 font-normal">No. Telepon</th>
                                <th class="px-4 py-3 font-normal text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-gray-600">
                            @foreach($users as $index => $user)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3 text-gray-400">{{ $users->firstItem() + $index }}</td>
                                <td class="px-4 py-3 flex flex-col gap-1">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-gray-200 overflow-hidden shrink-0">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" alt="">
                                        </div>
                                        <span class="text-gray-700 font-medium">{{ $user->name }}</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 ml-10">{{ $user->email }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $user->nip ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] {{ $user->role == 'head' ? 'bg-indigo-50 text-indigo-500' : 'bg-purple-50 text-purple-500' }}">
                                        {{ $user->role == 'head' ? 'Kepala Perpustakaan' : 'Petugas' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-400">{{ $user->phone ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-center gap-1">
                                        <button @click="openViewModal({{ $user }})" class="p-1.5 text-blue-400 hover:bg-blue-50 rounded-md" title="Lihat Detail"><i class="fas fa-eye"></i></button>
                                        <button @click="openEditModal({{ $user }})" class="p-1.5 text-orange-400 hover:bg-orange-50 rounded-md"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('head.users.destroy', $user->id) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-md" onclick="return confirm('Hapus user?')"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-white border-t border-gray-50 flex justify-between items-center text-gray-400 text-[10px]">
                    <p>Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data</p>
                    {{ $users->links('pagination::tailwind') }}
                </div>
            </div>

            <!-- 4. Form (Kanan) -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden sticky top-4">
                    <div class="p-4 border-b border-gray-50">
                        <h2 class="text-sm font-medium text-gray-700" x-text="formTitle"></h2>
                    </div>
                    <form :action="formAction" method="POST" id="userForm" class="p-4 space-y-3 text-xs">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div>
                            <label class="text-gray-500 mb-1 block">Nama Lengkap <span class="text-red-400">*</span></label>
                            <input type="text" name="name" x-model="name" placeholder="Masukkan nama lengkap" required class="w-full p-2 border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 text-gray-600">
                        </div>

                        <div>
                            <label class="text-gray-500 mb-1 block">NIP <span class="text-red-400">*</span></label>
                            <input type="text" name="nip" x-model="nip" placeholder="Masukkan NIP" required class="w-full p-2 border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 text-gray-600">
                        </div>
                        
                        <div>
                            <label class="text-gray-500 mb-1 block">Email <span class="text-red-400">*</span></label>
                            <input type="email" name="email" x-model="email" placeholder="contoh@sekolah.com" required class="w-full p-2 border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 text-gray-600">
                        </div>

                        <div>
                            <label class="text-gray-500 mb-1 block">No. Telepon</label>
                            <input type="text" name="phone" x-model="phone" placeholder="Masukkan nomor telepon" class="w-full p-2 border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 text-gray-600">
                        </div>

                        <div>
                            <label class="text-gray-500 mb-1 block">Password <span x-show="!isEdit" class="text-red-400">*</span></label>
                            <div class="relative">
                                <input type="password" 
                                    name="password" 
                                    id="password" 
                                    autocomplete="new-password"
                                    :required="!isEdit" 
                                    placeholder="Kosongkan jika tidak diubah" 
                                    class="w-full p-2 border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 text-gray-600">
                                <i class="fas fa-eye absolute right-3 top-2.5 text-gray-300"></i>
                            </div>
                        </div>

                        <div>
                            <label class="text-gray-500 mb-1 block">Konfirmasi Password <span x-show="!isEdit" class="text-red-400">*</span></label>
                            <div class="relative">
                                <input type="password" 
                                    name="password_confirmation" 
                                    id="password_confirmation" 
                                    autocomplete="new-password"
                                    :required="!isEdit" 
                                    placeholder="Ulangi password" 
                                    class="w-full p-2 border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-500 text-gray-600">
                                <i class="fas fa-eye absolute right-3 top-2.5 text-gray-300"></i>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg mt-4 flex items-center justify-center gap-2 hover:bg-blue-700 shadow-sm transition">
                            <i class="far fa-save"></i> Simpan User
                        </button>
                    </form>
                    @if ($errors->any())
                    <div class="bg-red-50 text-red-500 p-3 rounded-lg text-[11px]">
                        <p class="font-bold mb-1">Gagal menyimpan karena:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                </div>
            </div>
        </div> {{-- End of flex flex-col lg:flex-row gap-6 --}}

        {{-- MODAL VIEW USER DETAILS --}}
        <div x-show="showViewModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showViewModal = false" x-show="showViewModal" x-transition.opacity></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     x-show="showViewModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-white">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-user-circle text-blue-600"></i> Detail User: <span x-text="viewUser.name"></span></h3>
                        <button @click="showViewModal = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="p-5 space-y-4 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0">
                                <img :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(viewUser.name) + '&background=random'" alt="Avatar" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm" x-text="viewUser.name"></p>
                                <p class="text-gray-500" x-text="viewUser.email"></p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                <span class="text-gray-500 font-medium">NIP</span>
                                <span class="font-semibold text-gray-700" x-text="viewUser.nip || '-'"></span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                <span class="text-gray-500 font-medium">Peran</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                      :class="viewUser.role == 'head' ? 'bg-indigo-50 text-indigo-500' : 'bg-purple-50 text-purple-500'"
                                      x-text="viewUser.role == 'head' ? 'Kepala Perpustakaan' : 'Petugas'">
                                </span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                <span class="text-gray-500 font-medium">No. Telepon</span>
                                <span class="font-semibold text-gray-700" x-text="viewUser.phone || '-'"></span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                <span class="text-gray-500 font-medium">Dibuat Pada</span>
                                <span class="font-semibold text-gray-700" x-text="new Date(viewUser.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Terakhir Diperbarui</span>
                                <span class="font-semibold text-gray-700" x-text="new Date(viewUser.updated_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></span>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-gray-50/50 flex justify-end border-t border-gray-100 rounded-b-2xl">
                        <button type="button" @click="showViewModal = false" class="px-4 py-2 font-bold text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>