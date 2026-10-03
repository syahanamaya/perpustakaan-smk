<x-layouts.app title="Transaksi Peminjaman - Create">
    <div x-data="borrowingForm()" class="space-y-6">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Buat Transaksi Peminjaman</h1>
            <p class="text-gray-600">Masukkan data siswa dan pilih buku yang akan dipinjam</p>
        </div>

        <!-- Main Form -->
        <form @submit.prevent="submitForm" id="transactionForm" class="space-y-6">
            @csrf

            <!-- Section 1: Student Data -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-600">
                <div class="flex items-center mb-6">
                    <i class="fas fa-user-circle text-2xl text-blue-600 mr-3"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Data Siswa</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- NIS Search -->
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-2 text-blue-600"></i>Nomor Induk Siswa (NIS)
                        </label>
                        <div class="flex gap-2 items-center"> <!-- Tambahkan items-center -->
                            <input 
                                type="text" 
                                x-model="form.nis"
                                @keyup.enter="fetchStudent()"
                                placeholder="Cari NIS..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-smooth"
                            >
                            <button 
                                type="button"
                                @click="fetchStudent()"
                                :disabled="loading"
                                class="flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-smooth disabled:opacity-50"
                            > <!-- Tambahkan flex-none agar tombol tidak gepeng -->
                                <i class="fas fa-search" x-show="!loading"></i>
                                <i class="fas fa-spinner fa-spin" x-show="loading"></i> <!-- Spinner di dalam tombol lebih rapi -->
                            </button>
                        </div>
                    </div>

                    <!-- Student Name (Read-only) -->
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Nama Siswa
                        </label>
                        <input 
                            type="text" 
                            x-model="form.student_name"
                            readonly
                            placeholder="Nama akan ditampilkan di sini"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 cursor-not-allowed"
                        >
                    </div>

                    <!-- Class (Read-only) -->
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-graduation-cap mr-2 text-blue-600"></i>Kelas
                        </label>
                        <input 
                            type="text" 
                            x-model="form.student_class"
                            readonly
                            placeholder="Kelas akan ditampilkan di sini"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 cursor-not-allowed"
                        >
                    </div>

                    <!-- Student ID (Hidden) -->
                    <input type="hidden" x-model="form.student_id" name="student_id">
                </div>

                <!-- Error Message -->
                <div x-show="error" x-transition class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-exclamation-circle text-red-600"></i>
                    <span class="text-red-700" x-text="error"></span>
                </div>

                <!-- Loading State -->
                <div x-show="loading" x-transition class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-spinner fa-spin text-blue-600"></i>
                    <span class="text-blue-700">Mencari data siswa...</span>
                </div>
            </div>

            <!-- Section 2: Book Cart -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-600">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-shopping-cart text-2xl text-green-600 mr-3"></i>
                        <h2 class="text-xl font-semibold text-gray-800">Daftar Buku Peminjaman</h2>
                    </div>
                    <button 
                        type="button"
                        @click="addBookRow()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-smooth flex items-center space-x-2"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Tambah Buku</span>
                    </button>
                </div>

                <!-- Books Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 border-b-2 border-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 w-12">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kode Buku</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 flex-1">Judul Buku</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 w-24">Qty</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="(item, index) in form.items" :key="index">
                                <tr class="hover:bg-gray-50 transition-smooth">
                                    <td class="px-4 py-3 text-gray-600">
                                        <span x-text="index + 1"></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input 
                                        type="text" 
                                        x-model="item.book_code"
                                        @change="fetchBook(index)"     @keyup.enter="fetchBook(index)" placeholder="e.g., BK-001"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    >   
                                    </td>
                                    <td class="px-4 py-3">
                                        <input 
                                            type="text" 
                                            x-model="item.book_title"
                                            placeholder="Judul Buku"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <input 
                                            type="number" 
                                            x-model.number="item.qty"
                                            min="1"
                                            placeholder="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-center"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button 
                                            type="button"
                                            @click="removeBookRow(index)"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 px-3 py-1 rounded-lg transition-smooth"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr x-show="form.items.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                                    <p class="text-sm">Belum ada buku. Klik "Tambah Buku" untuk menambahkan</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-gray-700 font-medium">
                        <i class="fas fa-book text-green-600 mr-2"></i>
                        Total Buku: <span x-text="form.items.length" class="font-bold text-green-600"></span>
                    </p>
                </div>
            </div>

            <!-- Section 3: Dates & Submission -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-600">
                <div class="flex items-center mb-6">
                    <i class="fas fa-calendar text-2xl text-purple-600 mr-3"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Tanggal Peminjaman</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Borrow Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-purple-600"></i>Tanggal Peminjaman
                        </label>
                        <input 
                            type="date" 
                            x-model="form.borrow_date"
                            readonly
                            tabindex="-1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 cursor-not-allowed pointer-events-none"
                        >
                        <p class="text-xs text-gray-500 mt-1">Otomatis hari ini, tidak perlu diisi manual</p>
                    </div>

                    <!-- Return Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check mr-2 text-purple-600"></i>Tanggal Kembali
                        </label>
                        <input 
                            type="date" 
                            x-model="form.due_date"
                            :min="getTodayDate()"
                            :max="getMaxDueDate()"
                            @change="validateReturnDate()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                        <p class="text-xs text-gray-500 mt-1">Pilih tanggal harus kembali (maksimal 1 tahun)</p>
                    </div>
                </div>

                <!-- Return Date Error -->
                <div x-show="dateError" x-transition class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-start space-x-2">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                    <span class="text-yellow-700 text-sm" x-text="dateError"></span>
                </div>
            </div>

            <!-- Submission Section -->
            <div class="h-28"></div>

            <div class="fixed bottom-0 right-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 z-40 transition-all duration-300"
                 :class="sidebarOpen ? 'left-64' : 'left-0 md:left-20'">
                
                <div class="max-w-7xl mx-auto flex items-center justify-between px-4">
                    
                    <div class="hidden md:flex items-center text-sm">
                        <template x-if="!canSubmit()">
                            <div class="text-gray-500 flex items-center transition-opacity">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                <span>Lengkapi data siswa dan minimal 1 buku.</span>
                            </div>
                        </template>
                        <template x-if="canSubmit()">
                            <div class="text-green-600 flex items-center font-bold transition-opacity">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Data lengkap! Siap disimpan.</span>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-3 ml-auto">
                        <a href="/" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>

                        <button 
                            type="submit"
                            :disabled="!canSubmit() || submitting"
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold shadow-lg hover:shadow-blue-500/30 transition-all flex items-center disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                            
                            <i class="fas" :class="submitting ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                            
                            <span class="ml-2" x-text="submitting ? 'Menyimpan...' : 'Simpan Transaksi'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
    function borrowingForm() {
        return {
            loading: false,
            submitting: false,
            error: '',
            dateError: '',
            form: {
                nis: '',
                student_id: '',
                student_name: '',
                student_class: '',
                borrow_date: '',
                due_date: '',
                items: [
                    { book_code: '', book_title: '', book_id: '', qty: 1 }
                ]
            },

            init() {
                this.form.borrow_date = this.getTodayDate();
            },

            getTodayDate() {
                const d = new Date();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return d.getFullYear() + '-' + m + '-' + day;
            },

            getMaxDueDate() {
                const d = new Date();
                d.setFullYear(d.getFullYear() + 1);
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return d.getFullYear() + '-' + m + '-' + day;
            },

            fetchStudent() {
                if (!this.form.nis.trim()) {
                    this.error = 'Masukkan NIS terlebih dahulu';
                    return;
                }
                this.loading = true;
                this.error = '';

                fetch(`/transactions/check-student?nis=${this.form.nis}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.form.student_id = data.data.id;
                            this.form.student_name = data.data.name;
                            this.form.student_class = data.data.class; 
                            this.error = '';
                        } else {
                            this.error = data.message || 'Siswa tidak ditemukan';
                            this.form.student_id = '';
                            this.form.student_name = '';
                            this.form.student_class = '';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        this.error = 'Terjadi kesalahan sistem.';
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            // --- PERBAIKAN 2: FUNGSI PENCARIAN BUKU ---
            fetchBook(index) {
                const item = this.form.items[index];
                if (!item.book_code) return;

                // Tanda sedang loading judul
                this.form.items[index].book_title = 'Mencari...';

                fetch(`/transactions/search-book?book_code=${item.book_code}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // PENTING: Simpan ID dan Judul ke state Alpine
                            this.form.items[index].book_id = data.data.id; 
                            this.form.items[index].book_title = data.data.title;
                            // Reset error jika sebelumnya ada
                            this.error = ''; 
                        } else {
                            alert('Buku tidak ditemukan atau stok habis!');
                            this.form.items[index].book_code = '';
                            this.form.items[index].book_id = '';
                            this.form.items[index].book_title = '';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        this.form.items[index].book_title = '';
                    });
            },

            addBookRow() {
                this.form.items.push({
                    book_code: '',
                    book_title: '',
                    book_id: '', // Inisialisasi kosong
                    qty: 1
                });
            },

            removeBookRow(index) {
                this.form.items.splice(index, 1);
            },

            validateReturnDate() {
                if (!this.form.due_date) {
                    this.dateError = '';
                    return;
                }
                const borrowDate = new Date(this.form.borrow_date);
                const returnDate = new Date(this.form.due_date);
                const maxDate = new Date(this.getMaxDueDate());
                if (returnDate < borrowDate) {
                    this.dateError = 'Tanggal kembali harus setelah tanggal peminjaman';
                    this.form.due_date = '';
                } else if (returnDate > maxDate) {
                    this.dateError = 'Durasi peminjaman maksimal 1 tahun. Tanggal di luar batas tidak dapat dipilih.';
                    this.form.due_date = '';
                } else {
                    this.dateError = '';
                }
            },

            canSubmit() {
                return (
                    this.form.student_id &&
                    this.form.nis.trim() &&
                    this.form.student_name &&
                    this.form.due_date &&
                    this.form.items.length > 0 &&
                    // Pastikan book_id juga dicek
                    this.form.items.every(item => item.book_code && item.book_id && item.qty > 0) &&
                    !this.dateError
                );
            },

            submitForm() {
                if (!this.canSubmit()) {
                    this.error = 'Mohon lengkapi data (Siswa, Tanggal, dan Buku)';
                    return;
                }

                this.submitting = true;

                // --- PERBAIKAN 3: PASTIKAN book_id DIKIRIM ---
                const formData = {
                    student_id: this.form.student_id,
                    due_date: this.form.due_date,
                    items: this.form.items.map(item => ({
                        book_id: item.book_id, // <--- INI WAJIB ADA
                        qty: item.qty
                    }))
                };

                fetch('/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json', // Agar error dibaca sebagai JSON
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Transaksi Berhasil! Kode: ' + data.data.transaction_code);
                        window.location.href = '/transactions'; // Redirect ke index
                    } else {
                        // Tampilkan pesan error spesifik dari backend
                        this.error = data.message || 'Terjadi kesalahan validasi.';
                        console.log(data); // Cek console untuk debug
                    }
                })
                .catch(err => {
                    this.error = 'Error: ' + err.message;
                })
                .finally(() => {
                    this.submitting = false;
                });
            }
        };
    }
</script>

    @push('styles')
        <style>
            input[type="date"]::-webkit-calendar-picker-indicator {
                cursor: pointer;
            }
            
            input[readonly] {
                background-color: #f3f4f6;
            }
        </style>
    @endpush
</x-layouts.app>
