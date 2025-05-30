<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { defineProps, ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Eye, Search, ListFilter, BookCopy } from 'lucide-vue-next'; // PlusCircle dihapus dari impor jika tidak dipakai lagi

// ... (Interface Mahasiswa, StatusPengajuan, MataKuliahInfo, DetailHapusMkItem, PengajuanHapusMk, PaginatedData tetap sama) ...
interface StatusPengajuan {
    id: number;
    nama_status_display: string;
    kode_status?: string;
}
interface MataKuliahInfo {
    id: number;
    kode_mk: string;
    nama_mk: string;
}
interface DetailHapusMkItem {
    id: number;
    mata_kuliah: MataKuliahInfo;
}
interface PengajuanHapusMk {
    id: number;
    mahasiswa: Mahasiswa;
    jenis_pengajuan: string;
    perihal?: string | null;
    tanggal_pengajuan: string;
    current_status: StatusPengajuan;
    detail_hapus_mks?: DetailHapusMkItem[];
    jumlah_mk_diajukan?: number;
}
interface Mahasiswa {
    id: number;
    nama_lengkap?: string;
    nama?: string;
    nim: string;
    program_studi?: string;
}
interface PaginatedData<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean; }>;
    current_page: number; last_page: number; from: number; to: number; total: number; per_page: number;
}

const props = defineProps<{
    pengajuans: PaginatedData<PengajuanHapusMk>;
    pageTitle: string;
}>();

const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: props.pageTitle,
        href: route('admin.pengajuan.hapus-mk.index'),
        isCurrent: true,
    },
];

const searchTerm = ref('');

const filteredPengajuans = computed(() => {
    if (!searchTerm.value) {
        return props.pengajuans.data;
    }
    const lowerSearchTerm = searchTerm.value.toLowerCase();
    return props.pengajuans.data.filter(p =>
        (p.mahasiswa?.nama_lengkap?.toLowerCase().includes(lowerSearchTerm)) ||
        (p.mahasiswa?.nama?.toLowerCase().includes(lowerSearchTerm)) ||
        (p.mahasiswa?.nim?.toLowerCase().includes(lowerSearchTerm)) ||
        (p.mahasiswa?.program_studi?.toLowerCase().replace(/_/g, ' ').includes(lowerSearchTerm)) ||
        (p.perihal?.toLowerCase().includes(lowerSearchTerm)) ||
        (p.current_status?.nama_status_display?.toLowerCase().includes(lowerSearchTerm))
    );
});

const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    try {
        return new Date(dateString).toLocaleString('id-ID', options).replace(/\./g, ':');
    } catch (e) {
        return dateString;
    }
};

const getStatusClass = (kodeStatus?: string, namaStatus?: string) => {
    const lowerNamaStatus = namaStatus?.toLowerCase() || '';
    const lowerKodeStatus = kodeStatus?.toLowerCase() || '';
    if (lowerKodeStatus === 'disetujui' || lowerKodeStatus === 'selesai' || lowerNamaStatus.includes('disetujui') || lowerNamaStatus.includes('selesai')) {
        return 'bg-green-100 text-green-700 dark:bg-green-700/30 dark:text-green-300';
    }
    if (lowerKodeStatus === 'ditolak' || lowerKodeStatus === 'batal' || lowerNamaStatus.includes('ditolak') || lowerNamaStatus.includes('batal')) {
        return 'bg-red-100 text-red-700 dark:bg-red-700/30 dark:text-red-300';
    }
    if (lowerNamaStatus.includes('diproses') || lowerNamaStatus.includes('verifikasi')) {
        return 'bg-blue-100 text-blue-700 dark:bg-blue-700/30 dark:text-blue-300';
    }
    if (lowerNamaStatus.includes('menunggu') || lowerKodeStatus.includes('menunggu') || lowerNamaStatus.includes('diajukan')) {
        return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-700/30 dark:text-yellow-300';
    }
    if (lowerNamaStatus.includes('revisi') || lowerNamaStatus.includes('kembali')) {
        return 'bg-purple-100 text-purple-700 dark:bg-purple-700/30 dark:text-purple-300';
    }
    return 'bg-muted text-muted-foreground';
};

const formatProgramStudi = (prodi?: string) => {
    return prodi ? prodi.replace(/_/g, ' ') : '-';
}

const formatMataKuliahDiajukan = (details?: DetailHapusMkItem[]) => {
    if (!details || details.length === 0) {
        return '-';
    }
    return details
        .map(detail => detail.mata_kuliah?.kode_mk) 
        .filter(kodeMk => kodeMk) 
        .join(', ');
};


</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ pageTitle }}</h1>
                    <p class="text-sm text-muted-foreground">Kelola semua pengajuan hapus mata kuliah dari mahasiswa.</p>
                </div>
            </div>

            <div class="bg-card text-card-foreground shadow-md rounded-lg p-6 border border-border">
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


                <div v-if="!pengajuans || pengajuans.data.length === 0" class="text-center py-10 px-6">
                    <p class="text-muted-foreground text-lg">Tidak ada pengajuan hapus mata kuliah yang ditemukan.</p>
                </div>
                
                <div v-else class="overflow-x-auto">
                    <div v-if="filteredPengajuans.length === 0 && searchTerm" class="text-center py-10 px-6">
                        <p class="text-muted-foreground text-lg">Tidak ada pengajuan yang cocok dengan pencarian "{{ searchTerm }}".</p>
                    </div>
                    <table v-else class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">No.</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">NIM</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nama Mahasiswa</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Program Studi</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Perihal</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Mata Kuliah Diajukan</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tgl. Pengajuan</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            <tr v-for="(pengajuan, index) in filteredPengajuans" :key="pengajuan.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">
                                    {{ pengajuans.from ? (pengajuans.from + index) : (index + 1) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-foreground">
                                    {{ pengajuan.mahasiswa?.nim || '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-foreground font-medium">
                                    {{ pengajuan.mahasiswa?.nama_lengkap || pengajuan.mahasiswa?.nama || '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">
                                    {{ formatProgramStudi(pengajuan.mahasiswa?.program_studi) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-foreground">
                                    {{ pengajuan.perihal || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-foreground">
                                    <div v-if="pengajuan.detail_hapus_mks && pengajuan.detail_hapus_mks.length > 0" class="max-w-xs truncate" :title="formatMataKuliahDiajukan(pengajuan.detail_hapus_mks)">
                                        {{ formatMataKuliahDiajukan(pengajuan.detail_hapus_mks) }}
                                    </div>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">
                                    {{ formatDate(pengajuan.tanggal_pengajuan) }}
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
                                    <Link :href="route('admin.pengajuan.show', pengajuan.id)" class="text-primary hover:text-primary/80 inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-xs hover:bg-primary/10" title="Lihat Detail & Proses">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!searchTerm && pengajuans.data.length > 0 && pengajuans.links.length > 3" class="mt-6 flex flex-col sm:flex-row justify-between items-center text-sm text-muted-foreground">
                    <div>
                        Menampilkan {{ pengajuans.from }} sampai {{ pengajuans.to }} dari {{ pengajuans.total }} hasil
                    </div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px mt-4 sm:mt-0" aria-label="Pagination">
                        <template v-for="(link, key) in pengajuans.links" :key="key">
                            <div v-if="link.url === null"
                                 class="relative inline-flex items-center px-3 py-1.5 border border-border bg-card text-xs font-medium text-muted-foreground cursor-default"
                                 v-html="link.label"
                            />
                            <Link v-else
                                  :href="link.url"
                                  class="relative inline-flex items-center px-3 py-1.5 border border-border bg-card text-xs font-medium hover:bg-muted/50"
                                  :class="{'z-10 bg-primary/10 border-primary text-primary': link.active, 'text-foreground': !link.active}"
                                  v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>

            </div>
        </div>
    </AppLayout>
</template>