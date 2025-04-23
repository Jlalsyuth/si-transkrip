<template>
  <div class="hapus-matkul px-6 py-4">
    <!-- Header: Judul + Notifikasi + Profil -->
    <div class="flex justify-between items-center mb-1">
      <h1 class="text-2xl font-bold">Butuh Hapus Mata Kuliah?</h1>

      <div class="flex items-center gap-4">
        <!-- Notifikasi -->
        <button class="relative">
          <img src="/images/notification.svg" alt="Notifikasi" class="w-8 h-8" />
          <span class="absolute top-0 right-0 bg-red-500 text-white text-xs w-4 h-4 flex items-center justify-center rounded-full">1</span>
        </button>

        <!-- Profil -->
        <div class="flex items-center gap-2">
          <img src="/images/profile.png" alt="Profil" class="w-8 h-8 rounded-full object-cover" />
          <span class="text-sm text-gray-700">225150400111003</span>
          <img src="/images/arrow-down.svg" class="w-4 h-4" />
        </div>
      </div>
    </div>

    <!-- Subjudul -->
    <p class="text-gray-600 mb-6">Yuk, ajukan di sini. Pastikan datamu lengkap ya!</p>

    <!-- Section: Pengajuan Terbaru -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold">Pengajuan Terbaru</h2>

      <div class="flex items-center space-x-2">
        <!-- Tombol Tambah Pengajuan -->
        <button class="bg-[#D17742] text-white px-4 py-2 rounded-md hover:bg-opacity-90 transition">
          Tambah Pengajuan
        </button>

        <!-- Tombol Filter -->
        <button class="flex items-center border border-[#D17742] text-[#D17742] px-3 py-2 rounded-md text-sm hover:bg-[#D17742]/10 transition">
          <span class="mr-2">Filter</span>
          <img src="/images/filter.svg" class="w-4 h-4" />
        </button>
      </div>
    </div>

    <div>
      <p class="text-gray-500 mb-4">Lihat pengajuan yang baru saja diajukan.</p>
    </div>

    <!-- Tabel Pengajuan -->
    <table class="w-full border-collapse shadow rounded-md overflow-hidden">
      <thead class="bg-gray-100 text-gray-700 text-left">
        <tr>
          <th class="py-3 px-4">No.</th>
          <th class="py-3 px-4">Perihal</th>
          <th class="py-3 px-4">Mata Kuliah</th>
          <th class="py-3 px-4">Tanggal Pengajuan</th>
          <th class="py-3 px-4">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(pengajuan, index) in pengajuanList" :key="index" class="border-t hover:bg-gray-50">
          <td class="py-3 px-4">{{ index + 1 }}</td>
          <td class="py-3 px-4">{{ pengajuan.perihal }}</td>
          <td class="py-3 px-4">{{ pengajuan.matkul }}</td>
          <td class="py-3 px-4">{{ pengajuan.tanggal }}</td>
          <td class="py-3 px-4">
            <span class="bg-yellow-100 text-yellow-800 text-sm font-medium px-3 py-1 rounded-full">Diajukan</span>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Footer Tabel -->
    <div class="text-sm text-gray-500 text-right mt-2">1-{{ pengajuanList.length }}/{{ pengajuanList.length }}</div>
  </div>
</template>

<script>
export default {
  name: 'HapusMatkul',
  data() {
    return {
      pengajuanList: [
        {
          perihal: 'Pendaftaran Semhas dan Sidang Skripsi',
          matkul: 'Data Warehouse, Kecerdasan Bisnis dan Analitik, Evaluasi Pengalaman dan Antarmuka Pengguna',
          tanggal: '19/04/2025'
        },
        {
          perihal: 'Pendaftaran Ujian Khusus',
          matkul: 'Data Warehouse, Kecerdasan Bisnis dan Analitik, Evaluasi Pengalaman dan Antarmuka Pengguna',
          tanggal: '19/04/2025'
        }
      ]
    };
  }
};
</script>

<style scoped>
.material-icons {
  vertical-align: middle;
  font-size: 18px;
}
</style>
