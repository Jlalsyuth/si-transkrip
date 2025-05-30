<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'; // Gunakan layout yang sesuai
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, defineProps, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { UserCircle, Edit3, Mail, FileText, FileMinus2, AlertCircle, Clock, Eye, Filter } from 'lucide-vue-next';

// --- Interfaces (sesuaikan dengan data dari controller) ---
interface AuthUser { // Untuk KPS yang login
    id: number;
    nama: string;
    email: string;
    program_studi?: string; // Program studi yang diampu KPS
    profile_photo_url?: string;
    role?: string;
}
interface StatusInfo { id: number; nama_status_display: string; kode_status?: string; }
interface MahasiswaInfo { id: number; nama: string; nim: string; }

interface MataKuliahInfo { id: number; kode_mk: string; nama_mk: string; sks: number; }
interface DetailHapusMkItem { id: number; mata_kuliah: MataKuliahInfo; }

interface PengajuanDiproses {
    id: number;
    mahasiswa: MahasiswaInfo;
    jenis_pengajuan: string;
    tanggal_pengajuan: string;
    current_status: StatusInfo;
    perihal?: string | null;
    keperluan_transkrip?: string | null;
    bahasa_transkrip?: string | null;
    detail_hapus_mks?: DetailHapusMkItem[];
    jumlah_mk_diajukan?: number; // Ditambahkan oleh transform di controller
}
interface PaginatedPengajuan<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean; }>;
    current_page: number; last_page: number; from: number; to: number; total: number; per_page: number;
}

const page = usePage();
const kpsUser = computed(() => page.props.auth?.user as AuthUser | undefined);

const props = defineProps<{
    pageTitle: string;
    statsTranskripMenungguKPS: number;
    statsHapusMkMenungguKPS: number;
    pengajuanPerluPersetujuan: PaginatedPengajuan<PengajuanDiproses>;
    namaStatusFilterAktif?: string; // Nama status yang sedang ditampilkan di tabel
}>();

const breadcrumbs = [
    { title: 'KPS Dashboard', href: route('kps.dashboard'), isCurrent: true },
];

// State untuk filter tabel
const filterJenisPengajuan = ref<'transkrip_sementara' | 'hapus_mata_kuliah' | 'semua'>('semua');

const filteredPengajuanUntukTabel = computed(() => {
    if (!props.pengajuanPerluPersetujuan || !props.pengajuanPerluPersetujuan.data) {
        return [];
    }
    if (filterJenisPengajuan.value === 'semua') {
        return props.pengajuanPerluPersetujuan.data;
    }
    return props.pengajuanPerluPersetujuan.data.filter(p => p.jenis_pengajuan === filterJenisPengajuan.value);
});

// Fungsi Helper (bisa diimpor jika sudah ada di file terpisah)
const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const options: Intl.DateTimeFormatOptions = { day: '2-digit', month: 'long', year: 'numeric', hour:'2-digit', minute:'2-digit' };
    try { return new Date(dateString).toLocaleDateString('id-ID', options); }
    catch (e) { return "Invalid Date"; }
};
const formatJenisPengajuan = (jenis: string) => {
    if (jenis === 'transkrip_sementara') return 'Transkrip Sementara';
    if (jenis === 'hapus_mata_kuliah') return 'Hapus Mata Kuliah';
    return jenis ? jenis.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-';
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
const formatMataKuliahDiajukan = (details?: DetailHapusMkItem[]) => {
    if (!details || details.length === 0) return '-';
    return details.map(detail => detail.mata_kuliah?.kode_mk).filter(kodeMk => kodeMk).join(', ');
};
const formatProgramStudiKPS = (prodi?: string) => {
    return prodi ? prodi.replace(/_/g, ' ') : 'Semua Prodi';
}

</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <h1 class="text-3xl font-bold text-foreground">
                Selamat Datang, KPS {{ formatProgramStudiKPS(kpsUser?.program_studi) }}!
                <span class="inline-block origin-[70%_70%] animate-[wave_2.5s_ease-in-out_infinite]">👋</span>
            </h1>
            <p class="text-muted-foreground -mt-4 mb-2">
                Berikut adalah ringkasan pengajuan yang memerlukan persetujuan Anda.
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <Card class="lg:col-span-1 border-border shadow-sm">
                    <CardHeader>
                        <CardTitle class="text-lg">Profil KPS</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-col items-center text-center">
                        <img v-if="kpsUser?.profile_photo_url" :src="kpsUser.profile_photo_url" alt="Foto Profil" class="w-24 h-24 rounded-full object-cover mb-3 border-2 border-primary">
                        <UserCircle v-else class="w-24 h-24 text-muted-foreground mb-3" />
                        <p class="text-xl font-semibold text-foreground">{{ kpsUser?.nama || 'Nama KPS' }}</p>
                        <p class="text-sm text-muted-foreground">{{ kpsUser?.email }}</p>
                        <p v-if="kpsUser?.program_studi" class="mt-1 text-xs bg-accent text-accent-foreground px-2 py-0.5 rounded-full inline-block">
                            KPS {{ formatProgramStudiKPS(kpsUser.program_studi) }}
                        </p>
                        <Link :href="route('profile.edit')" class="w-full mt-4">
                            <Button variant="outline" class="w-full border-border text-foreground hover:bg-accent hover:text-accent-foreground">
                                <Edit3 class="h-4 w-4 mr-2"/> Edit Profil
                            </Button>
                        </Link>
                    </CardContent>
                </Card>

                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card class="border-border shadow-sm">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">Pengajuan Transkrip (Menunggu Anda)</CardTitle>
                            <FileText class="h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-foreground">
                                {{ props.statsTranskripMenungguKPS || 0 }}
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">Memerlukan persetujuan KPS</p>
                        </CardContent>
                    </Card>
                    <Card class="border-border shadow-sm">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">Pengajuan Hapus MK (Menunggu Anda)</CardTitle>
                            <FileMinus2 class="h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-foreground">
                                 {{ props.statsHapusMkMenungguKPS || 0 }}
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">Memerlukan persetujuan KPS</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
            
            <div class="bg-card text-card-foreground shadow-md rounded-lg p-6 border border-border">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                    <h2 class="text-lg font-semibold text-foreground">
                        Pengajuan Perlu Diproses
                    </h2>
                    <div class="flex gap-2">
                        <Button 
                            @click="filterJenisPengajuan = 'semua'" 
                            :variant="filterJenisPengajuan === 'semua' ? 'default' : 'outline'"
                            size="sm"
                            class="border-border"
                            :class="{'bg-primary text-primary-foreground': filterJenisPengajuan === 'semua'}">
                            Semua Jenis
                        </Button>
                        <Button 
                            @click="filterJenisPengajuan = 'transkrip_sementara'" 
                            :variant="filterJenisPengajuan === 'transkrip_sementara' ? 'default' : 'outline'"
                            size="sm"
                             class="border-border"
                            :class="{'bg-primary text-primary-foreground': filterJenisPengajuan === 'transkrip_sementara'}">
                            Transkrip
                        </Button>
                        <Button 
                            @click="filterJenisPengajuan = 'hapus_mata_kuliah'" 
                            :variant="filterJenisPengajuan === 'hapus_mata_kuliah' ? 'default' : 'outline'"
                            size="sm"
                             class="border-border"
                            :class="{'bg-primary text-primary-foreground': filterJenisPengajuan === 'hapus_mata_kuliah'}">
                            Hapus MK
                        </Button>
                    </div>
                </div>

                <div v-if="!filteredPengajuanUntukTabel || filteredPengajuanUntukTabel.length === 0" class="text-center py-10">
                    <Clock class="mx-auto h-12 w-12 text-muted-foreground mb-2" />
                    <p class="text-muted-foreground">
                        {{ (props.pengajuanPerluPersetujuan && props.pengajuanPerluPersetujuan.data.length > 0) ? 'Tidak ada data sesuai filter.' : 'Tidak ada pengajuan yang perlu diproses saat ini.' }}
                    </p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground uppercase">Mahasiswa</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground uppercase">Jenis</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground uppercase">Detail Permohonan</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground uppercase">Tgl. Diajukan</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="item in filteredPengajuanUntukTabel" :key="item.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-foreground">{{ item.mahasiswa.nama }}</div>
                                    <div class="text-muted-foreground text-xs">{{ item.mahasiswa.nim }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-foreground">{{ formatJenisPengajuan(item.jenis_pengajuan) }}</td>
                                <td class="px-4 py-3 text-foreground text-xs">
                                    <div v-if="item.jenis_pengajuan === 'transkrip_sementara'">
                                        Keperluan: {{ item.keperluan_transkrip || '-' }} <br>
                                        Bahasa: {{ item.bahasa_transkrip || '-' }}
                                    </div>
                                    <div v-if="item.jenis_pengajuan === 'hapus_mata_kuliah'">
                                        Perihal: {{ item.perihal || '-' }} <br>
                                        MK: {{ formatMataKuliahDiajukan(item.detail_hapus_mks) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">{{ formatDate(item.tanggal_pengajuan) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-medium">
                                    <Link :href="route('kps.pengajuan.show', item.id)" class="text-primary hover:text-primary/80 font-semibold">
                                        Lihat & Proses
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                 <div v-if="props.pengajuanPerluPersetujuan.data.length > 0 && props.pengajuanPerluPersetujuan.links.length > 3" class="mt-6 flex flex-col sm:flex-row justify-between items-center text-sm text-muted-foreground">
                    <div>
                        Menampilkan {{ props.pengajuanPerluPersetujuan.from }} sampai {{ props.pengajuanPerluPersetujuan.to }} dari {{ props.pengajuanPerluPersetujuan.total }} hasil
                    </div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px mt-4 sm:mt-0" aria-label="Pagination">
                        <template v-for="(link, key) in props.pengajuanPerluPersetujuan.links" :key="key">
                            <div v-if="link.url === null"
                                 class="relative inline-flex items-center px-3 py-1.5 border border-border bg-card text-xs font-medium text-muted-foreground cursor-default"
                                 v-html="link.label"
                            />
                            <Link v-else
                                  :href="link.url"
                                  @click="filterJenisPengajuan = 'semua'" 
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

<style scoped>
@keyframes wave-animation { /* ... (sama seperti sebelumnya) ... */ }
.animate-\[wave_2\.5s_ease-in-out_infinite\] { animation: wave-animation 2.5s ease-in-out infinite; }
</style>