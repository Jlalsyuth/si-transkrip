<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'; // Pastikan path ini sesuai
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import { Button } from '@/components/ui/button';
// [MODIFIKASI] Tambahkan Download
import { ArrowLeft, Paperclip, ListCollapse, UserCircle, CalendarDays, MessageSquareText, Download } from 'lucide-vue-next';

// Types (sama seperti sebelumnya, pastikan konsisten dengan data dari controller)
interface User {
    id: number;
    nama: string;
    nim?: string;
}

interface StatusPengajuan {
    id: number;
    nama_status_display: string;
    kode_status?: string;
}

interface MataKuliah {
    id: number;
    kode_mk: string;
    nama_mk: string;
    sks: number;
}

interface DetailHapusMk {
    id: number;
    mata_kuliah: MataKuliah;
    status_persetujuan_kps: string;
    alasan_tolak_kps?: string;
}

interface TrackingPengajuan {
    id: number;
    status: StatusPengajuan;
    user_pelaku?: User | { nama: string };
    catatan_tracking?: string;
    timestamp_perubahan: string;
}

interface Pengajuan {
    id: number;
    mahasiswa: User;
    jenis_pengajuan: string;
    keperluan_pengajuan_transkrip?: { nama_keperluan: string } | string | null;
    bahasa_transkrip?: 'indonesia' | 'inggris' | null;
    tanggal_pengajuan: string;
    current_status: StatusPengajuan;
    catatan_mahasiswa?: string;
    catatan_operator?: string;
    catatan_kps?: string;
    file_transkrip_sementara_unggah_mhs_url?: string; // File unggahan awal mahasiswa
  // file_transkrip_final_operator_url?: string; // Ini tidak akan ditampilkan lagi untuk download oleh mahasiswa
    file_ttd_kps_url?: string | null;               // [TAMBAHKAN/MODIFIKASI] File final yang sudah di-TTD KPS
    detail_hapus_mks?: DetailHapusMk[];
    tracking_pengajuans: TrackingPengajuan[];
    created_at: string;
}

const props = defineProps<{
    pengajuan: Pengajuan;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Daftar Pengajuan Saya', href: route('pengajuan.index') },
    { title: `Detail Pengajuan #${props.pengajuan.id}`, href: route('pengajuan.show', props.pengajuan.id), isCurrent: true },
];

const formatJenisPengajuan = (jenis: string) => {
    if (jenis === 'transkrip_sementara') return 'Transkrip Sementara';
    if (jenis === 'hapus_mata_kuliah') return 'Hapus Mata Kuliah';
    return jenis.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
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

const formatKeperluan = (keperluan: any) => {
    if (typeof keperluan === 'object' && keperluan !== null && keperluan.nama_keperluan) {
        return keperluan.nama_keperluan;
    }
    if (typeof keperluan === 'string') {
        return keperluan;
    }
    return '-';
};

const getPelakuName = (pelaku: User | { nama: string } | undefined) => {
    if (!pelaku) return 'Sistem';
    return pelaku.nama || 'User Tidak Dikenal';
}

</script>


<template>
    <Head :title="`Detail Pengajuan #${pengajuan.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">
                        Detail Pengajuan #{{ pengajuan.id }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Jenis: {{ formatJenisPengajuan(pengajuan.jenis_pengajuan) }}
                    </p>
                </div>
                <Link :href="route('pengajuan.index')">
                    <Button variant="outline" class="border-border text-foreground hover:bg-accent hover:text-accent-foreground">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Kembali ke Daftar
                    </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-card text-card-foreground shadow-md rounded-lg p-6 border border-border">
                    <h2 class="text-lg font-semibold border-b border-border pb-2 mb-4">Informasi Pengajuan</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <p class="text-muted-foreground">Tanggal Diajukan:</p>
                            <p class="font-medium">{{ formatDate(pengajuan.tanggal_pengajuan) }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Status Saat Ini:</p>
                            <span :class="getStatusClass(pengajuan.current_status.kode_status, pengajuan.current_status.nama_status_display)" class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ pengajuan.current_status.nama_status_display }}
                            </span>
                        </div>
                        <template v-if="pengajuan.jenis_pengajuan === 'transkrip_sementara'">
                                <div>
                            <p class="text-muted-foreground">Keperluan Transkrip:</p>
                            <p class="font-medium">{{ formatKeperluan(pengajuan.keperluan_pengajuan_transkrip) }}</p>
                                </div>
                            <div>
                                <p class="text-muted-foreground">Bahasa Transkrip:</p>
                                <p class="font-medium">{{ pengajuan.bahasa_transkrip ? pengajuan.bahasa_transkrip.charAt(0).toUpperCase() + pengajuan.bahasa_transkrip.slice(1) : '-' }}</p>
                            </div>
                        </template>
                        <div class="sm:col-span-2">
                            <p class="text-muted-foreground">Catatan Mahasiswa:</p>
                            <p class="whitespace-pre-wrap">{{ pengajuan.catatan_mahasiswa || '-' }}</p>
                        </div>
                        <div v-if="pengajuan.catatan_operator" class="sm:col-span-2">
                            <p class="text-muted-foreground">Catatan Operator:</p>
                            <p class="whitespace-pre-wrap">{{ pengajuan.catatan_operator }}</p>
                        </div>
                        <div v-if="pengajuan.catatan_kps" class="sm:col-span-2">
                            <p class="text-muted-foreground">Catatan KPS:</p>
                            <p class="whitespace-pre-wrap">{{ pengajuan.catatan_kps }}</p>
                        </div>
                    </div>

                                        <div class="mt-6 pt-4 border-t border-border">
                        <h3 class="text-md font-semibold mb-2">File Terkait</h3>
                        <ul class="space-y-2 text-sm">
                            <li v-if="pengajuan.file_transkrip_sementara_unggah_mhs_url">
                                <a :href="pengajuan.file_transkrip_sementara_unggah_mhs_url" target="_blank" class="text-primary hover:text-primary/80 hover:underline flex items-center">
                                    <Paperclip class="h-4 w-4 mr-2 shrink-0" />
                                    File Pendukung Unggahan Saya
                                </a>
                            </li>

                            <template v-if="pengajuan.jenis_pengajuan === 'transkrip_sementara' || pengajuan.jenis_pengajuan === 'hapus_mata_kuliah'">
                                    <li v-if="pengajuan.file_ttd_kps_url">
                                        <a :href="pengajuan.file_ttd_kps_url"
                                           target="_blank"
                                           download
                                           class="text-green-600 dark:text-green-400 hover:underline flex items-center font-medium">
                                            <Download class="h-4 w-4 mr-2 shrink-0" />
                                            <span v-if="pengajuan.jenis_pengajuan === 'transkrip_sementara'">Unduh Transkrip Final (dari KPS)</span>
                                            <span v-else-if="pengajuan.jenis_pengajuan === 'hapus_mata_kuliah'">Unduh Dokumen Persetujuan (dari KPS)</span>
                                        </a>
                                    </li>
                                    <li v-else>                                         
                                        <p class="text-muted-foreground">
                                            <span v-if="pengajuan.jenis_pengajuan === 'transkrip_sementara'">Transkrip final</span>
                                            <span v-else-if="pengajuan.jenis_pengajuan === 'hapus_mata_kuliah'">Dokumen persetujuan</span>
                                            yang telah ditandatangani KPS belum tersedia.
                                        </p>
                                    </li>
                            </template>
                        </ul>
                        <p v-if="!pengajuan.file_transkrip_sementara_unggah_mhs_url && pengajuan.jenis_pengajuan !== 'transkrip_sementara'" class="text-sm text-muted-foreground mt-2">
                            Tidak ada file terkait.
                        </p>
                    </div>
                    <div v-if="pengajuan.jenis_pengajuan === 'hapus_mata_kuliah' && pengajuan.detail_hapus_mks && pengajuan.detail_hapus_mks.length > 0" class="mt-6 pt-4 border-t border-border">
                        <h3 class="text-md font-semibold mb-3">Detail Mata Kuliah Diajukan Hapus</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">Kode MK</th>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">Nama Mata Kuliah</th>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">SKS</th>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">Persetujuan KPS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="detailMk in pengajuan.detail_hapus_mks" :key="detailMk.id">
                                        <td class="px-4 py-2 text-foreground">{{ detailMk.mata_kuliah.kode_mk }}</td>
                                        <td class="px-4 py-2 text-foreground">{{ detailMk.mata_kuliah.nama_mk }}</td>
                                        <td class="px-4 py-2 text-foreground">{{ detailMk.mata_kuliah.sks }}</td>
                                        <td class="px-4 py-2">
                                            <span :class="getStatusClass(undefined, detailMk.status_persetujuan_kps)" class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ detailMk.status_persetujuan_kps.charAt(0).toUpperCase() + detailMk.status_persetujuan_kps.slice(1) }}
                                            </span>
                                            <p v-if="detailMk.alasan_tolak_kps" class="text-xs text-destructive mt-1">Alasan: {{ detailMk.alasan_tolak_kps }}</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-card text-card-foreground shadow-md rounded-lg p-6 lg:sticky lg:top-24 border border-border">
                    <h2 class="text-lg font-semibold border-b border-border pb-2 mb-4 flex items-center">
                        <ListCollapse class="h-5 w-5 mr-2 text-muted-foreground" />
                        Riwayat Pengajuan
                    </h2>
                    <ul v-if="pengajuan.tracking_pengajuans && pengajuan.tracking_pengajuans.length > 0" class="space-y-4">
                        <li v-for="track in pengajuan.tracking_pengajuans" :key="track.id" class="relative pl-8">
                            <span :class="getStatusClass(track.status.kode_status, track.status.nama_status_display)" class="absolute left-0 top-1 flex items-center justify-center w-4 h-4 rounded-full -translate-x-1/2 ring-4 ring-card">
                            </span>
                            <div class="p-3 bg-muted/30 rounded-lg shadow-sm">
                                <p class="text-sm font-semibold text-foreground">{{ track.status.nama_status_display }}</p>
                                <time class="text-xs text-muted-foreground flex items-center">
                                    <CalendarDays class="h-3 w-3 mr-1" /> {{ formatDate(track.timestamp_perubahan) }}
                                </time>
                                <p v-if="track.user_pelaku" class="text-xs text-muted-foreground mt-0.5 flex items-center">
                                    <UserCircle class="h-3 w-3 mr-1" />Oleh: {{ getPelakuName(track.user_pelaku) }}
                                </p>
                                <p v-if="track.catatan_tracking" class="mt-1 text-xs whitespace-pre-wrap bg-muted/50 p-2 rounded">
                                    <MessageSquareText class="h-3 w-3 mr-1 inline-block text-muted-foreground" /> {{ track.catatan_tracking }}
                                </p>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">Belum ada riwayat untuk pengajuan ini.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>