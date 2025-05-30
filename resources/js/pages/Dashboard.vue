<script setup lang="ts">
// ... (impor dan script setup lainnya yang sudah ada)
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, defineProps } from 'vue';
import { Button } from '@/components/ui/button';
import { FileText, FileMinus2, UserCircle, NotebookText, GraduationCap, Mail, ArrowRight, Edit3, Clock } from 'lucide-vue-next';

// ... (interface yang sudah ada)
interface AuthUser {
    id: number;
    nama_lengkap?: string;
    nama?: string;
    nim?: string;
    email: string;
    program_studi?: string;
    profile_photo_url?: string;
}

interface StatusPengajuan {
    id: number;
    nama_status_display: string;
    kode_status?: string;
}
interface KeperluanPengajuan {
    id: number;
    nama_keperluan: string;
}
interface Pengajuan {
    id: number;
    keperluan_transkrip?: string | null;
    keperluan_pengajuan_transkrip?: KeperluanPengajuan | null;
    jenis_pengajuan: string;
    tanggal_pengajuan: string;
    current_status: StatusPengajuan;
}


const page = usePage();
const user = computed(() => page.props.auth?.user as AuthUser | undefined);

const props = defineProps<{
    recentPengajuans: Pengajuan[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        isCurrent: true,
    },
];

// ... (fungsi helper formatDate, getStatusClass, formatJenisPengajuan, formatKeperluan tetap sama)
const formatJenisPengajuan = (jenis: string) => {
    if (jenis === 'transkrip_sementara') return 'Transkrip Sementara';
    if (jenis === 'hapus_mata_kuliah') return 'Hapus Mata Kuliah';
    return jenis.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatKeperluan = (pengajuan: Pengajuan) => {
    if (pengajuan.jenis_pengajuan === 'transkrip_sementara') {
        return pengajuan.keperluan_transkrip || '-';
    }
    return '-';
};

const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: '2-digit', day: '2-digit' };
    try {
        return new Date(dateString).toLocaleDateString('id-ID', options);
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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="mb-2">
                <h1 class="text-3xl font-bold text-foreground">Halo {{ user?.nama_lengkap || user?.nama || 'Mahasiswa' }}!! 👋</h1>
                <p class="text-muted-foreground">Semoga harimu menyenangkan. Yuk, kelola transkrip akademikmu hari ini!</p>
            </div>

            <div class="grid auto-rows-auto gap-6 md:grid-cols-3">
                <div class="md:col-span-1 bg-card text-card-foreground rounded-xl border border-border shadow-sm p-6 flex flex-col">
                    <h2 class="text-lg font-semibold text-foreground mb-4">Profil Pengguna</h2>
                    <div class="flex flex-col items-center text-center mb-4">
                        <img v-if="user?.profile_photo_url" :src="user.profile_photo_url" alt="Foto Profil" class="w-24 h-24 rounded-full object-cover mb-3 border-2 border-primary">
                        <UserCircle v-else class="w-24 h-24 text-muted-foreground mb-3" />
                        <p class="text-xl font-semibold text-foreground">{{ user?.nama_lengkap || user?.nama || 'Nama Pengguna' }}</p>
                        <p class="text-sm text-muted-foreground">{{ user?.nim || 'NIM tidak tersedia' }}</p>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center">
                            <Mail class="w-4 h-4 mr-2 text-muted-foreground shrink-0" />
                            <span class="text-muted-foreground mr-1">Email:</span>
                            <span class="text-foreground truncate">{{ user?.email }}</span>
                        </div>
                        <div class="flex items-center">
                            <GraduationCap class="w-4 h-4 mr-2 text-muted-foreground shrink-0" />
                            <span class="text-muted-foreground mr-1">Prodi:</span>
                            <span class="text-foreground">{{ user?.program_studi ? user.program_studi.replace(/_/g, ' ') : '-' }}</span>
                        </div>
                    </div>
                    <div class="mt-auto pt-4">
                        <Link :href="route('profile.edit')" class="w-full">
                            <Button variant="outline" class="w-full border-border text-foreground hover:bg-accent hover:text-accent-foreground">
                                <Edit3 class="h-4 w-4 mr-2"/> Edit Profil
                            </Button>
                        </Link>
                    </div>
                </div>

                <div class="md:col-span-2 bg-card text-card-foreground rounded-xl border border-border shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">Quick Menu</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Link :href="route('pengajuan.create')" class="block p-6 bg-background hover:bg-muted/50 rounded-lg border border-border transition-all duration-200 ease-in-out transform hover:scale-105">
                            <div class="flex items-center mb-2">
                                <FileText class="w-8 h-8 text-primary mr-3" />
                                <h3 class="text-md font-semibold text-foreground">Ajukan Transkrip Sementara</h3>
                            </div>
                            <p class="text-xs text-muted-foreground">Ajukan transkrip sementara dengan cepat sesuai keperluan kamu.</p>
                        </Link>
                        <Link :href="route('pengajuan.hapus-mk.create')" class="block p-6 bg-background hover:bg-muted/50 rounded-lg border border-border transition-all duration-200 ease-in-out transform hover:scale-105">
                            <div class="flex items-center mb-2">
                                <FileMinus2 class="w-8 h-8 text-primary mr-3" />
                                <h3 class="text-md font-semibold text-foreground">Ajukan Hapus Mata Kuliah</h3>
                            </div>
                            <p class="text-xs text-muted-foreground">Perbaiki KRS-mu, hapus mata kuliah di sini.</p>
                        </Link>
                    </div>
                </div>
            </div>
            
            <div class="bg-card text-card-foreground rounded-xl border border-border shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-foreground">Pengajuan Terbaru</h2>
                    <Link :href="route('pengajuan.index')" class="text-sm text-primary hover:underline">
                        Lihat Semua
                    </Link>
                </div>
                <div v-if="!props.recentPengajuans || props.recentPengajuans.length === 0" class="text-center py-8">
                    <Clock class="mx-auto h-12 w-12 text-muted-foreground" />
                    <p class="mt-2 text-sm text-muted-foreground">Belum ada pengajuan terbaru.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">No.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Keperluan/Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            <tr v-for="(item, index) in props.recentPengajuans" :key="item.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">{{ index + 1 }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-foreground">
                                    <span class="font-medium">{{ formatJenisPengajuan(item.jenis_pengajuan) }}</span>
                                    <span v-if="item.jenis_pengajuan === 'transkrip_sementara'" class="block text-xs text-muted-foreground">{{ formatKeperluan(item) }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(item.tanggal_pengajuan) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(item.current_status.kode_status, item.current_status.nama_status_display)">
                                        {{ item.current_status.nama_status_display }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('pengajuan.show', item.id)" class="text-primary hover:text-primary/80 flex items-center justify-end gap-1">
                                        Lihat Detail <ArrowRight class="h-3 w-3" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>