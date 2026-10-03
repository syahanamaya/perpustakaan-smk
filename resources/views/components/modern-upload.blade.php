<div x-data="{ isDropping: false, fileName: '' }" class="w-full">
    <label 
        for="file-upload" 
        class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-xl cursor-pointer transition-colors duration-200 ease-in-out relative overflow-hidden"
        :class="isDropping ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50 hover:bg-gray-100'"
        @dragover.prevent="isDropping = true"
        @dragleave.prevent="isDropping = false"
        @drop.prevent="isDropping = false; document.getElementById('file-upload').files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0].name"
    >
        <div class="flex flex-col items-center justify-center pt-5 pb-6">
            <!-- Ikon berubah warna saat di hover drag-drop -->
            <i class="fas fa-cloud-upload-alt text-4xl mb-3 transition-colors" :class="isDropping ? 'text-blue-500' : 'text-gray-400'"></i>
            
            <p class="mb-2 text-sm text-gray-500 font-semibold" x-show="!fileName">
                <span class="text-blue-600">Klik untuk upload</span> atau Drag and Drop file
            </p>
            <p class="mb-2 text-sm text-blue-600 font-bold text-center px-4 truncate w-full" x-show="fileName" x-text="fileName"></p>
            
            <p class="text-xs text-gray-500" x-show="!fileName">XLSX, XLS, atau CSV (Maks. 5MB)</p>
        </div>
        
        <!-- Input disembunyikan -->
        <input 
            id="file-upload" 
            name="file" 
            type="file" 
            class="hidden" 
            accept=".xlsx, .xls, .csv"
            @change="fileName = $event.target.files[0].name"
        />
    </label>
</div>