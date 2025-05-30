<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, defineProps } from 'vue'; // onMounted & watch tidak lagi diperlukan untuk persetujuan_mk individual
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
// Ganti dengan elemen textarea HTML standar jika komponen kustom Textarea tidak ada
// import { Textarea } from '@/components/ui/textarea';
import { 
    ArrowLeft, Paperclip, ListCollapse, UserCircle, CalendarDays, MessageSquareText, 
    Mail, NotebookText, GraduationCap, Download, UploadCloud, Save 
} from 'lucide-vue-next';

// --- Interfaces ---
interface User { 
    id: number; 
    nama: string; 
    nim?: string; 
    email?: string; 
    program_studi?: string; 
}
interface StatusPengajuan { 
    id: number; 
    nama_status_display: string; 
    kode_status?: string; 
}
interface MataKuliahInfo { 
    id: number; 
    kode_mk: string; 
    nama_mk: string; 
    sks: number; 
}
interface DetailHapusMkItem {
    id: number;
    mata_kuliah: MataKuliahInfo;
    status_persetujuan_kps: 'menunggu' | 'disetujui' | 'ditolak'; // Status dari DB (untuk info)
    alasan_tolak_kps?: string | null; // Alasan dari DB (untuk info)
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
    jenis_pengajuan: 'hapus_mata_kuliah';
    perihal?: string | null;
    sks_lulus_total?: string | number | null;
    sks_lulus_wajib?: string | number | null;
    sks_lulus_pilihan?: string | number | null;
    tanggal_pengajuan: string;
    current_status: StatusPengajuan;
    catatan_mahasiswa?: string;
    catatan_operator?: string;
    catatan_kps?: string; // Catatan KPS yang sudah ada
    file_pendukung_mahasiswa_url?: string | null; 
    file_dari_operator_url?: string | null; // File dari Admin (misal surat keputusan untuk di TTD KPS)
    file_ttd_kps_url?: string | null; // File yang sudah di TTD KPS sebelumnya
    detail_hapus_mks: DetailHapusMkItem[]; // Daftar mata kuliah yang diajukan hapus
    tracking_pengajuans: TrackingPengajuan[];
}

const props = defineProps<{
    pengajuan: Pengajuan;
    statusOptionsForKPS: Array<{ id: number; nama_status_display: string }>;
    pageTitle: string;
}>();

const kpsActionForm = useForm({
    _method: 'POST', 
    new_status_id: props.pengajuan.current_status.id,
    catatan_kps: props.pengajuan.catatan_kps || '',
    file_tanda_tangan_kps: null as File | null, // Untuk KPS mengunggah file baru
    // Tidak ada lagi persetujuan_mk per item di sini
});

// Inisialisasi form dengan catatan KPS yang sudah ada
// onMounted(() => {
// kpsActionForm.catatan_kps = props.pengajuan.catatan_kps || '';
// kpsActionForm.new_status_id = props.pengajuan.current_status.id;
// });


const breadcrumbs = [
    { title: 'KPS Dashboard', href: route('kps.dashboard') },
    { title: 'Daftar Pengajuan KPS', href: '#' }, // Ganti '#' dengan route daftar pengajuan KPS (misal kps.pengajuan.hapus-mk.index)
    { title: props.pageTitle, href: route('kps.pengajuan.show', { pengajuan: props.pengajuan.id }), isCurrent: true },
];

// --- Fungsi Helper ---
const formatDate = (dateString: string): string => {
    if (!dateString) return '-';
    const options: Intl.DateTimeFormatOptions = { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    try { return new Date(dateString).toLocaleDateString('id-ID', options); } 
    catch (e) { return "Invalid Date"; }
};
const getStatusClass = (kodeStatus?: string, namaStatus?: string): string => {
    const lowerNamaStatus = namaStatus?.toLowerCase() || '';
    const lowerKodeStatus = kodeStatus?.toLowerCase() || '';
    if (lowerKodeStatus === 'disetujui' || lowerKodeStatus === 'selesai' || lowerNamaStatus.includes('disetujui') || lowerNamaStatus.includes('selesai')) { return 'bg-green-100 text-green-700 dark:bg-green-700/30 dark:text-green-300';}
    if (lowerKodeStatus === 'ditolak' || lowerKodeStatus === 'batal' || lowerNamaStatus.includes('ditolak') || lowerNamaStatus.includes('batal')) { return 'bg-red-100 text-red-700 dark:bg-red-700/30 dark:text-red-300';}
    if (lowerNamaStatus.includes('diproses') || lowerNamaStatus.includes('verifikasi')) { return 'bg-blue-100 text-blue-700 dark:bg-blue-700/30 dark:text-blue-300';}
    if (lowerNamaStatus.includes('menunggu') || lowerKodeStatus.includes('menunggu') || lowerNamaStatus.includes('diajukan')) { return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-700/30 dark:text-yellow-300';}
    if (lowerNamaStatus.includes('revisi') || lowerNamaStatus.includes('kembali')) { return 'bg-purple-100 text-purple-700 dark:bg-purple-700/30 dark:text-purple-300';}
    return 'bg-muted text-muted-foreground';
};
const getPelakuName = (pelaku: User | { nama: string } | undefined): string => {
    if (!pelaku) return 'Sistem';
    return pelaku.nama || 'User Tidak Dikenal';
};
const formatProgramStudi = (prodi?: string): string => {
    return prodi ? prodi.replace(/_/g, ' ') : '-';
};
// Akhir Fungsi Helper

const handleKpsFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        kpsActionForm.file_tanda_tangan_kps = target.files[0];
    } else {
        kpsActionForm.file_tanda_tangan_kps = null;
    }
};

const submitKpsAction = () => {
    kpsActionForm.post(route('kps.pengajuan.hapus-mk.proses', props.pengajuan.id), {
        preserveScroll: true,
        onSuccess: () => {
            kpsActionForm.file_tanda_tangan_kps = null;
        },
    });
};
</script>

<template>
    <Head :title="pageTitle" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ pageTitle }}</h1>
                    <p class="text-sm text-muted-foreground">
                        Mahasiswa: {{ pengajuan.mahasiswa?.nama }} ({{ pengajuan.mahasiswa?.nim }}) - Hapus Mata Kuliah
                    </p>
                </div>
                <Link :href="route('kps.dashboard')">
                    <Button variant="outline" class="border-border text-foreground hover:bg-accent hover:text-accent-foreground">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Kembali
                    </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-card text-card-foreground shadow-md rounded-lg p-6 border border-border">
                    <div class="mb-6 pb-4 border-b border-border">
                        <h3 class="text-md font-semibold text-foreground mb-2">Data Mahasiswa</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                            <p><strong class="text-muted-foreground w-32 inline-block">Nama:</strong> {{ pengajuan.mahasiswa?.nama || '-' }}</p>
                            <p><strong class="text-muted-foreground w-32 inline-block">NIM:</strong> {{ pengajuan.mahasiswa?.nim || '-' }}</p>
                            <p><strong class="text-muted-foreground w-32 inline-block">Prodi:</strong> {{ formatProgramStudi(pengajuan.mahasiswa?.program_studi) }}</p>
                            <p><strong class="text-muted-foreground w-32 inline-block">Email:</strong> {{ pengajuan.mahasiswa?.email || '-' }}</p>
                        </div>
                    </div>

                    <h3 class="text-md font-semibold text-foreground mb-2">Detail Pengajuan Hapus Mata Kuliah</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm mb-6">
                        <div><p class="text-muted-foreground">Tgl. Diajukan:</p><p class="font-medium">{{ formatDate(pengajuan.tanggal_pengajuan) }}</p></div>
                        <div><p class="text-muted-foreground">Status Saat Ini:</p><span :class="getStatusClass(pengajuan.current_status.kode_status, pengajuan.current_status.nama_status_display)" class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full">{{ pengajuan.current_status.nama_status_display }}</span></div>
                        <div><p class="text-muted-foreground">Perihal:</p><p class="font-medium">{{ pengajuan.perihal || '-' }}</p></div>
                        <div class="sm:col-span-2"><p class="text-muted-foreground">SKS Lulus (Total/Wajib/Pilihan):</p><p class="font-medium">{{ pengajuan.sks_lulus_total ?? '-' }} / {{ pengajuan.sks_lulus_wajib ?? '-' }} / {{ pengajuan.sks_lulus_pilihan ?? '-' }}</p></div>
                        <div class="sm:col-span-2" v-if="pengajuan.catatan_mahasiswa"><p class="text-muted-foreground">Catatan Mahasiswa:</p><p class="whitespace-pre-wrap p-2 bg-muted/20 rounded">{{ pengajuan.catatan_mahasiswa }}</p></div>
                        <div v-if="pengajuan.catatan_operator" class="sm:col-span-2"><p class="text-muted-foreground">Catatan Operator:</p><p class="whitespace-pre-wrap p-2 bg-muted/30 rounded">{{ pengajuan.catatan_operator }}</p></div>
                        <div v-if="pengajuan.catatan_kps && kpsActionForm.catatan_kps !== pengajuan.catatan_kps" class="sm:col-span-2"><p class="text-muted-foreground">Catatan KPS (Sebelumnya):</p><p class="whitespace-pre-wrap p-2 bg-amber-100 dark:bg-amber-900/30 rounded">{{ pengajuan.catatan_kps }}</p></div>
                    </div>

                    <div class="mb-6 pb-4 border-b border-border" v-if="pengajuan.file_pendukung_mahasiswa_url || pengajuan.file_dari_operator_url || pengajuan.file_ttd_kps_url">
                        <h3 class="text-md font-semibold text-foreground mb-2">File Lampiran</h3>
                        <ul class="space-y-2 text-sm">
                            <li v-if="pengajuan.file_pendukung_mahasiswa_url">
                                <a :href="pengajuan.file_pendukung_mahasiswa_url" target="_blank" class="text-primary hover:text-primary/80 hover:underline flex items-center">
                                    <Paperclip class="h-4 w-4 mr-2 shrink-0" /> File Pendukung dari Mahasiswa
                                </a>
                            </li>
                            <li v-if="pengajuan.file_dari_operator_url">
                                <a :href="pengajuan.file_dari_operator_url" target="_blank" class="text-primary hover:text-primary/80 hover:underline flex items-center">
                                    <Download class="h-4 w-4 mr-2 shrink-0" /> Dokumen dari Operator (untuk TTD KPS)
                                </a>
                            </li>
                             <li v-if="pengajuan.file_ttd_kps_url">
                                <p class="text-muted-foreground mb-1 text-xs">File Keputusan (Sudah ditandatangani KPS):</p>
                                <a :href="pengajuan.file_ttd_kps_url" target="_blank" class="text-green-600 dark:text-green-400 hover:underline flex items-center">
                                    <Paperclip class="h-4 w-4 mr-2 shrink-0" /> Lihat File TTD KPS
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-md font-semibold text-foreground mb-3">Mata Kuliah Diajukan untuk Dihapus:</h3>
                        <div class="space-y-3">
                            <div v-if="!pengajuan.detail_hapus_mks || pengajuan.detail_hapus_mks.length === 0">
                                <p class="text-sm text-muted-foreground">Tidak ada detail mata kuliah yang diajukan.</p>
                            </div>
                            <div v-for="detailMk in pengajuan.detail_hapus_mks" :key="detailMk.id" class="p-3 border border-border rounded-md bg-background">
                                <p class="font-semibold text-foreground">{{ detailMk.mata_kuliah.nama_mk }} ({{ detailMk.mata_kuliah.kode_mk }}) - {{ detailMk.mata_kuliah.sks }} SKS</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Status Persetujuan KPS Sebelumnya: 
                                    <span :class="getStatusClass(undefined, detailMk.status_persetujuan_kps)" class="px-1.5 py-0.5 rounded-full font-medium">
                                        {{ detailMk.status_persetujuan_kps || 'Belum diproses' }}
                                    </span>
                                    <span v-if="detailMk.alasan_tolak_kps" class="block text-destructive/80">Alasan Sebelumnya: {{ detailMk.alasan_tolak_kps }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submitKpsAction" class="mt-6 pt-6 border-t border-border space-y-6">
                        <h3 class="text-lg font-semibold text-foreground mb-1">Tindakan KPS (Keseluruhan Pengajuan)</h3>
                        <div>
                            <Label for="kps_new_status_id" class="text-foreground">Ubah Status Pengajuan <span class="text-destructive">*</span></Label>
                            <select 
                                id="kps_new_status_id" 
                                v-model="kpsActionForm.new_status_id"
                                required
                                class="mt-1 block w-full px-3 py-2 bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-ring focus:border-primary text-sm text-foreground"
                            >
                                <option disabled value="">Pilih status baru...</option>
                                <option v-for="status in props.statusOptionsForKPS" :key="status.id" :value="status.id">
                                    {{ status.nama_status_display }}
                                </option>
                            </select>
                            <InputError :message="kpsActionForm.errors.new_status_id" class="mt-1 text-destructive" />
                        </div>
                        <div>
                            <Label for="catatan_kps_form" class="text-foreground">Catatan KPS (Opsional)</Label>
                            <textarea
                                id="catatan_kps_form"
                                v-model="kpsActionForm.catatan_kps"
                                rows="4"
                                class="mt-1 block w-full px-3 py-2 bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-ring focus:border-primary text-sm text-foreground"
                                placeholder="Tambahkan catatan Anda sebagai KPS..."
                            ></textarea>
                            <InputError :message="kpsActionForm.errors.catatan_kps" class="mt-1 text-destructive" />
                        </div>
                        <div>
                            <Label for="file_ttd_kps" class="text-foreground">Upload File Keputusan/TTD KPS (PDF, maks 2MB)</Label>
                            <div class="mt-1">
                                <label class="w-full flex flex-col items-center px-4 py-6 bg-background border-2 border-dashed border-input text-muted-foreground rounded-lg shadow-sm tracking-wide cursor-pointer hover:border-primary hover:text-primary">
                                    <UploadCloud class="w-8 h-8 mb-2" />
                                    <span class="text-sm leading-normal">{{ kpsActionForm.file_tanda_tangan_kps ? kpsActionForm.file_tanda_tangan_kps.name : 'Pilih atau seret file...' }}</span>
                                    <input id="file_ttd_kps" type="file" @change="handleKpsFileChange" class="hidden" accept=".pdf" />
                                </label>
                            </div>
                            <InputError :message="kpsActionForm.errors.file_tanda_tangan_kps" class="mt-1 text-destructive" />
                        </div>
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="kpsActionForm.processing" class="bg-primary hover:bg-primary/90 text-primary-foreground">
                                <Save class="h-4 w-4 mr-2" />
                                Simpan Keputusan KPS
                            </Button>
                        </div>
                    </form>
                </div>

                <div class="bg-card text-card-foreground shadow-md rounded-lg p-6 lg:sticky lg:top-24 border border-border">
                     <h2 class="text-lg font-semibold border-b border-border pb-2 mb-4 flex items-center">
                        <ListCollapse class="h-5 w-5 mr-2 text-muted-foreground" />
                        Riwayat Pengajuan
                    </h2>
                    <ul v-if="pengajuan.tracking_pengajuans && pengajuan.tracking_pengajuans.length > 0" class="space-y-4">
                        <li v-for="track in pengajuan.tracking_pengajuans" :key="track.id" class="relative pl-8">
                            <span :class="getStatusClass(track.status.kode_status, track.status.nama_status_display)" class="absolute left-0 top-1 flex items-center justify-center w-4 h-4 rounded-full -translate-x-1/2 ring-4 ring-card"></span>
                            <div class="p-3 bg-muted/30 rounded-lg shadow-sm">
                                <p class="text-sm font-semibold text-foreground">{{ track.status.nama_status_display }}</p>
                                <time class="text-xs text-muted-foreground flex items-center"><CalendarDays class="h-3 w-3 mr-1" /> {{ formatDate(track.timestamp_perubahan) }}</time>
                                <p v-if="track.user_pelaku" class="text-xs text-muted-foreground mt-0.5 flex items-center"><UserCircle class="h-3 w-3 mr-1" />Oleh: {{ getPelakuName(track.user_pelaku) }}</p>
                                <p v-if="track.catatan_tracking" class="mt-1 text-xs whitespace-pre-wrap bg-muted/50 p-2 rounded"><MessageSquareText class="h-3 w-3 mr-1 inline-block text-muted-foreground" /> {{ track.catatan_tracking }}</p>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">Belum ada riwayat untuk pengajuan ini.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
textarea {
    line-height: 1.5; 
}
.dark textarea {
    background-color: hsl(var(--background));
    color: hsl(var(--foreground));
    border-color: hsl(var(--input));
}
.dark textarea::placeholder {
    color: hsl(var(--muted-foreground));
}
textarea::placeholder {
     color: hsl(var(--muted-foreground) / 0.6);
}
</style>