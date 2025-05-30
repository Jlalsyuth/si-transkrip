<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { defineProps, ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
// Impor komponen UI dan ikon yang mungkin Anda perlukan
import { Eye, Search, ListFilter } from 'lucide-vue-next'; // Contoh

// --- Interfaces (sesuaikan dengan data dari controller) ---
interface User { id: number; nama: string; nim?: string; program_studi?: string; }
interface StatusPengajuan { id: number; nama_status_display: string; kode_status?: string; }
interface MataKuliahInfo { id: number; kode_mk: string; nama_mk: string; }
interface DetailHapusMkItem { id: number; mata_kuliah: MataKuliahInfo; }

interface PengajuanRiwayat { // Tipe data untuk item di riwayat
    id: number;
    mahasiswa: User;
    jenis_pengajuan: string;
    perihal?: string | null; // Untuk hapus MK
    keperluan_transkrip?: string | null; // Untuk transkrip
    tanggal_pengajuan: string; // Tanggal awal pengajuan dibuat mahasiswa
    current_status: StatusPengajuan; // Status pengajuan saat ini (mungkin sudah berubah lagi setelah aksi KPS)
    status_oleh_kps?: StatusPengajuan | null; // Status yang ditetapkan oleh KPS pada aksi terakhirnya
    tanggal_diproses_kps?: string | null; // Tanggal KPS terakhir melakukan aksi
    jumlah_mk_diajukan?: number; // Untuk hapus MK
    detail_hapus_mks?: DetailHapusMkItem[]; // Untuk menampilkan MK di kolom jika perlu
}

interface PaginatedData<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean; }>;
    current_page: number; last_page: number; from: number; to: number; total: number; per_page: number;
}

const props = defineProps<{
    pengajuans: PaginatedData<PengajuanRiwayat>; 
    pageTitle: string;
    error_message?: string;
}>();

const breadcrumbs = [
    { title: 'KPS Dashboard', href: route('kps.dashboard') },
    { title: props.pageTitle, href: route('kps.riwayat.index'), isCurrent: true },
];

const searchTerm = ref(''); // Untuk search jika ingin ditambahkan

const filteredPengajuans = computed(() => {
    if (!props.pengajuans?.data) return [];
    if (!searchTerm.value) {
        return props.pengajuans.data;
    }
    const lowerSearchTerm = searchTerm.value.toLowerCase();
    return props.pengajuans.data.filter(p =>
        (p.mahasiswa?.nama?.toLowerCase().includes(lowerSearchTerm)) ||
        (p.mahasiswa?.nim?.toLowerCase().includes(lowerSearchTerm)) ||
        (p.perihal && p.perihal.toLowerCase().includes(lowerSearchTerm)) || // Check if perihal exists
        (p.keperluan_transkrip && p.keperluan_transkrip.toLowerCase().includes(lowerSearchTerm)) || // Check if keperluan_transkrip exists
        (p.current_status?.nama_status_display?.toLowerCase().includes(lowerSearchTerm)) ||
        (p.status_oleh_kps?.nama_status_display?.toLowerCase().includes(lowerSearchTerm))
    );
});

// --- Fungsi Helper ---
const formatJenisPengajuan = (jenis: string) => {
    if (jenis === 'transkrip_sementara') return 'Transkrip Sementara';
    if (jenis === 'hapus_mata_kuliah') return 'Hapus Mata Kuliah';
    return jenis.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatDate = (dateString: string | null | undefined) => { // Allow null or undefined
    if (!dateString) return '-';
    const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    try { return new Date(dateString).toLocaleDateString('id-ID', options); }
    catch (e) { return "Invalid Date"; }
};

const getStatusClass = (kodeStatus?: string, namaStatus?: string) => {
    const lowerNamaStatus = namaStatus?.toLowerCase() || '';
    const lowerKodeStatus = kodeStatus?.toLowerCase() || '';
    if (lowerKodeStatus === 'disetujui' || lowerKodeStatus === 'selesai' || lowerNamaStatus.includes('disetujui') || lowerNamaStatus.includes('selesai')) { return 'bg-green-100 text-green-700 dark:bg-green-700/30 dark:text-green-300';}
    if (lowerKodeStatus === 'ditolak' || lowerKodeStatus === 'batal' || lowerNamaStatus.includes('ditolak') || lowerNamaStatus.includes('batal')) { return 'bg-red-100 text-red-700 dark:bg-red-700/30 dark:text-red-300';}
    if (lowerNamaStatus.includes('diproses') || lowerNamaStatus.includes('verifikasi')) { return 'bg-blue-100 text-blue-700 dark:bg-blue-700/30 dark:text-blue-300';}
    if (lowerNamaStatus.includes('menunggu') || lowerKodeStatus.includes('menunggu') || lowerNamaStatus.includes('diajukan')) { return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-700/30 dark:text-yellow-300';}
    if (lowerNamaStatus.includes('revisi') || lowerNamaStatus.includes('kembali')) { return 'bg-purple-100 text-purple-700 dark:bg-purple-700/30 dark:text-purple-300';}
    return 'bg-muted text-muted-foreground';
};

const getPelakuName = (pelaku: User | { nama: string } | undefined) => {
    if (!pelaku) return 'Sistem';
    return pelaku.nama || 'User Tidak Dikenal';
};
const formatMataKuliahDiajukan = (details?: DetailHapusMkItem[]) => {
    if (!details || details.length === 0) return '-';
    return details.map(detail => detail.mata_kuliah?.kode_mk).filter(Boolean).join(', ');
};

const formatProgramStudi = (prodi?: string) => {
    return prodi ? prodi.replace(/_/g, ' ') : '-';
};
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ pageTitle }}</h1>
                    <p class="text-sm text-muted-foreground">Daftar pengajuan yang telah Anda proses/tindaklanjuti.</p>
                </div>
            </div>

            <div v-if="props.error_message" class="bg-destructive/10 text-destructive p-4 rounded-md">
                {{ props.error_message }}
            </div>

            <div v-else class="bg-card text-card-foreground shadow-md rounded-lg border border-border">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center mb-4">
                        <div class="relative w-full sm:max-w-xs">
                            <Input 
                                type="text"
                                v-model="searchTerm"
                                placeholder="Cari (NIM, Nama, Perihal, Status)..."
                                class="w-full pl-10"
                            />
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        </div>
                        <Button variant="outline" class="border-border text-foreground hover:bg-accent hover:text-accent-foreground w-full sm:w-auto">
                            <ListFilter class="h-4 w-4 mr-2" />
                            Filter
                        </Button>
                    </div>
                </div>

                <div v-if="!filteredPengajuans || filteredPengajuans.length === 0" class="text-center py-10 px-6">
                    <p class="text-muted-foreground text-lg">
                        {{ searchTerm ? 'Tidak ada riwayat yang cocok dengan pencarian.' : 'Belum ada riwayat persetujuan.' }}
                    </p>
                </div>
                
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">No.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Mahasiswa</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Detail Permohonan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tgl. Diajukan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tgl. Diproses KPS</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status oleh KPS</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status Saat Ini</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            <tr v-for="(pengajuan, index) in filteredPengajuans" :key="pengajuan.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">
                                    {{ props.pengajuans.from ? (props.pengajuans.from + index) : (index + 1) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <div class="font-medium text-foreground">{{ pengajuan.mahasiswa?.nama || '-' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ pengajuan.mahasiswa?.nim || '-' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ formatProgramStudi(pengajuan.mahasiswa?.program_studi) }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-foreground">{{ formatJenisPengajuan(pengajuan.jenis_pengajuan) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-foreground">
                                    <div v-if="pengajuan.jenis_pengajuan === 'transkrip_sementara'">Keperluan: {{ pengajuan.keperluan_transkrip || '-' }}</div>
                                    <div v-if="pengajuan.jenis_pengajuan === 'hapus_mata_kuliah'">
                                        Perihal: {{ pengajuan.perihal || '-' }} <br>
                                        MK: <span class="text-xs text-muted-foreground">{{ formatMataKuliahDiajukan(pengajuan.detail_hapus_mks) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(pengajuan.tanggal_pengajuan) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(pengajuan.tanggal_diproses_kps) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <span v-if="pengajuan.status_oleh_kps"
                                        class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="getStatusClass(pengajuan.status_oleh_kps.kode_status, pengajuan.status_oleh_kps.nama_status_display)"
                                    >
                                        {{ pengajuan.status_oleh_kps.nama_status_display }}
                                    </span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <span
                                        class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="getStatusClass(pengajuan.current_status.kode_status, pengajuan.current_status.nama_status_display)"
                                    >
                                        {{ pengajuan.current_status.nama_status_display }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                    <Link :href="route('kps.pengajuan.show', pengajuan.id)" class="text-primary hover:text-primary/80 inline-flex items-center px-2 py-1" title="Lihat Detail">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!searchTerm && props.pengajuans && props.pengajuans.data.length > 0 && props.pengajuans.links.length > 3" class="mt-6 flex flex-col sm:flex-row justify-between items-center text-sm text-muted-foreground">
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Style tambahan jika perlu */
</style>