<template>
    <div class="p-8 bg-white rounded-xl shadow max-w-4xl mx-auto">
      <h1 class="text-2xl font-bold mb-6">Form Hapus Mata Kuliah</h1>
      <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block font-medium mb-1">NIM</label>
            <input v-model="form.nim" type="text" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-orange-200" />
          </div>
  
          <div>
            <label class="block font-medium mb-1">Nama</label>
            <input v-model="form.nama" type="text" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-orange-200" />
          </div>
  
          <div>
            <label class="block font-medium mb-1">Program Studi</label>
            <input v-model="form.program_studi" type="text" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-orange-200" />
          </div>
  
          <div>
            <label class="block font-medium mb-1">Email</label>
            <input v-model="form.email" type="email" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-orange-200" />
          </div>
  
          <div>
            <label class="block font-medium mb-1">Jumlah SKS</label>
            <input v-model="form.jumlah_sks" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-orange-200" />
          </div>
  
          <div>
            <label class="block font-medium mb-1">SKS Lulus Mata Kuliah Wajib</label>
            <input v-model="form.sks_wajib" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-orange-200" />
          </div>
  
          <div>
            <label class="block font-medium mb-1">SKS Lulus Mata Kuliah Pilihan</label>
            <input v-model="form.sks_pilihan" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-orange-200" />
          </div>
          <div>
            <label class="font-semibold">Perihal</label>
            <div class="flex flex-col gap-2 mt-1">
                <label class="flex items-center gap-2">
                <input type="radio" value="Semhas" v-model="form.perihal" />
                Pendaftaran Semhas dan Sidang Skripsi
                </label>
                <label class="flex items-center gap-2">
                <input type="radio" value="Ujian Khusus" v-model="form.perihal" />
                Pendaftaran Ujian Khusus
                </label>
            </div>
            </div>

        </div>
  

  
        <div>
          <label class="font-semibold">Mata Kuliah yang Ingin Dihapus</label>
          <div class="flex gap-2 mt-1">
            <input
              type="text"
              v-model="matkulBaru"
              placeholder="Tuliskan nama mata kuliah"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-orange-200"
            />
            <button type="button" @click="tambahMatkul" class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                <img src="/images/plus.svg" alt="Tambah" class="w-5 h-5" />
            </button>
          </div>
          <ul class="list-disc list-inside mt-2 text-sm">
            <li v-for="(mk, i) in form.matkul" :key="i">{{ mk }}</li>
          </ul>
        </div>
  
        <div>
        <label class="font-semibold block mb-1">Upload Transkrip</label>
        <div
            @dragover.prevent="dragActive = true"
            @dragleave.prevent="dragActive = false"
            @drop.prevent="handleDrop"
            @click="triggerFileSelect"
            :class="[
            'w-full h-40 border-2 border-dashed rounded-md flex flex-col justify-center items-center cursor-pointer transition',
            dragActive ? 'border-orange-500 bg-orange-50' : 'border-gray-300'
            ]"
        >
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M7 16V4a1 1 0 011-1h8a1 1 0 011 1v12m-5 4l-4-4m0 0l4-4m-4 4h12" />
            </svg>
            <p class="text-sm text-gray-600 text-center">
            Klik atau seret file PDF ke sini untuk mengunggah
            </p>
            <p v-if="form.file" class="text-xs text-gray-500 mt-2">{{ form.file.name }}</p>
        </div>
        <input
            ref="fileInput"
            type="file"
            accept="application/pdf"
            class="hidden"
            @change="handleFileUpload"
        />

        <p class="text-sm text-gray-500 mt-1">Max 1mb. Hanya file PDF.</p>
        </div>

  
        <div class="text-right">
          <button
            type="submit"
            class="bg-orange-600 hover:bg-orange-700 text-white font-semibold px-6 py-2 rounded-md"
          >
            Submit
          </button>
        </div>
      </form>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { useForm } from '@inertiajs/vue3'

    const fileInput = ref(null)
    const dragActive = ref(false)
  
  const form = useForm({
    nim: '',
    nama: '',
    email: '',
    program_studi: '',
    jumlah_sks: '',
    sks_wajib: '',
    sks_pilihan: '',
    perihal: '',
    matkul: [],
    file: null,
  })
  
  const matkulBaru = ref('')
  
  function tambahMatkul() {
    if (matkulBaru.value.trim()) {
      form.matkul.push(matkulBaru.value.trim())
      matkulBaru.value = ''
    }
  }
  
  function triggerFileSelect() {
  if (fileInput.value) fileInput.value.click()
}

function handleDrop(e) {
  dragActive.value = false
  const droppedFile = e.dataTransfer.files[0]
  if (droppedFile && droppedFile.type === 'application/pdf') {
    form.file = droppedFile
  } else {
    alert('Hanya file PDF yang diperbolehkan.')
  }
}

function handleFileUpload(e) {
  const selectedFile = e.target.files[0]
  if (selectedFile && selectedFile.type === 'application/pdf') {
    form.file = selectedFile
  } else {
    alert('Hanya file PDF yang diperbolehkan.')
  }
}
  
  function submit() {
    form.post('/hapus-matkul', {
      preserveScroll: true,
      onSuccess: () => alert('Berhasil dikirim!'),
      onError: () => alert('Gagal mengirim!')
    })
  }
  </script>
  