<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, defineProps } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
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
    // Tambahkan role jika ingin ditampilkan di info KPS yg login
    role?: string;
    profile_photo_url?: string;
}

interface StatusPengajuan {
    id: number;
    nama_status_display: string;
    kode_status?: string;
}

interface TrackingPengajuan {
    id: number;
    status: StatusPengajuan;
    user_pelaku?: User | { nama: string }; // Pelaku bisa sistem atau user
    catatan_tracking?: string;
    timestamp_perubahan: string;
}

interface Pengajuan {
    id: number;
    mahasiswa: User; // Informasi mahasiswa yang mengajukan
    jenis_pengajuan: string; // Seharusnya 'transkrip_sementara' untuk halaman ini
    keperluan_transkrip?: string | null;
    bahasa_transkrip?: 'indonesia' | 'inggris' | null;
    tanggal_pengajuan: string;
    current_status: StatusPengajuan;
    catatan_mahasiswa?: string;
    catatan_operator?: string;
    catatan_kps?: string; // Catatan KPS yang sudah ada
    file_dari_operator_url?: string | null; // URL file DARI OPERATOR (sebelumnya file_transkrip_final_operator_url)
    file_ttd_kps_url?: string | null;      // URL file YANG SUDAH DI-TTD KPS (dari kolom file_tanda_tangan_kps)
    tracking_pengajuans: TrackingPengajuan[];
    created_at: string;
}

const page = usePage();
// KPS yang sedang login
const kpsUser = computed(() => page.props.auth?.user as User | undefined);

const props = defineProps<{
    pengajuan: Pengajuan;
    statusOptionsForKPS: Array<{ id: number; nama_status_display: string }>;
    pageTitle: string;
}>();

const kpsActionForm = useForm({
    _method: 'POST', // Atau 'PATCH' jika route Anda PATCH/PUT, Inertia akan handle method spoofing
    new_status_id: props.pengajuan.current_status.id,
    catatan_kps: props.pengajuan.catatan_kps || '',
    file_tanda_tangan_kps: null as File | null, // Untuk KPS mengunggah file baru
});

const breadcrumbs = [
    { title: 'KPS Dashboard', href: route('kps.dashboard') },
    // Tambahkan link ke daftar pengajuan KPS jika ada halaman terpisah
    // { title: 'Daftar Pengajuan', href: route('kps.pengajuan.index') }, 
    { title: props.pageTitle, href: route('kps.pengajuan.show', props.pengajuan.id), isCurrent: true },
];

// --- Fungsi Helper ---
const formatDate = (dateString: string) => {
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

const formatProgramStudi = (prodi?: string) => {
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
    // Nama route ini harus sesuai dengan yang Anda definisikan di routes/web.php untuk aksi KPS
    kpsActionForm.post(route('kps.pengajuan.transkrip.proses', props.pengajuan.id), {
        preserveScroll: true,
        onSuccess: () => {
            kpsActionForm.file_tanda_tangan_kps = null; // Reset file input setelah sukses
            // Inertia akan otomatis me-refresh data props jika controller melakukan redirect
            // atau Anda bisa paksa reload jika perlu:
            // Inertia.reload({ only: ['pengajuan'] });
        },
        onError: (errors) => {
            console.error('Error saat memproses pengajuan oleh KPS:', errors);
        }
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
                        Mahasiswa: {{ pengajuan.mahasiswa?.nama }} ({{ pengajuan.mahasiswa?.nim }})
                    </p>
                </div>
                <Link :href="route('kps.dashboard')">
                    <Button variant="outline" class="border-border text-foreground hover:bg-accent hover:text-accent-foreground">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Kembali ke Dashboard KPS
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
                    <h3 class="text-md font-semibold text-foreground mb-2">Detail Pengajuan Transkrip</h3>
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
                        <div>
                            <p class="text-muted-foreground">Keperluan Transkrip:</p>
                            <p class="font-medium">{{ pengajuan.keperluan_transkrip || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Bahasa Transkrip:</p>
                            <p class="font-medium">{{ pengajuan.bahasa_transkrip ? pengajuan.bahasa_transkrip.charAt(0).toUpperCase() + pengajuan.bahasa_transkrip.slice(1) : '-' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-muted-foreground">Catatan Mahasiswa:</p>
                            <p class="whitespace-pre-wrap">{{ pengajuan.catatan_mahasiswa || '-' }}</p>
                        </div>
                        <div v-if="pengajuan.catatan_operator" class="sm:col-span-2">
                            <p class="text-muted-foreground">Catatan Operator:</p>
                            <p class="whitespace-pre-wrap bg-muted/30 p-2 rounded">{{ pengajuan.catatan_operator }}</p>
                        </div>
                         <div v-if="pengajuan.catatan_kps && kpsActionForm.catatan_kps !== pengajuan.catatan_kps" class="sm:col-span-2"> {/* Tampilkan catatan KPS lama jika beda dengan yang di form */}
                            <p class="text-muted-foreground">Catatan KPS (Sebelumnya):</p>
                            <p class="whitespace-pre-wrap bg-amber-50 dark:bg-amber-900/30 p-2 rounded">{{ pengajuan.catatan_kps }}</p>
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-border">
                        <h3 class="text-md font-semibold mb-2">File Terkait</h3>
                        <ul class="space-y-3 text-sm">
                            <li v-if="pengajuan.file_dari_operator_url">
                                <p class="text-muted-foreground mb-1">File Transkrip dari Operator (untuk ditinjau & ditandatangani):</p>
                                <a :href="pengajuan.file_dari_operator_url" target="_blank" class="inline-flex items-center text-primary hover:text-primary/80 font-medium hover:underline">
                                    <Download class="h-4 w-4 mr-2 shrink-0" /> Download File dari Operator
                                </a>
                            </li>
                             <li v-if="pengajuan.file_ttd_kps_url">
                                <p class="text-muted-foreground mb-1">File Transkrip (Sudah ditandatangani KPS):</p>
                                <a :href="pengajuan.file_ttd_kps_url" target="_blank" class="inline-flex items-center text-green-600 dark:text-green-400 hover:underline">
                                    <Paperclip class="h-4 w-4 mr-2 shrink-0" /> Lihat File TTD KPS
                                </a>
                            </li>
                            <li v-if="!pengajuan.file_dari_operator_url && !pengajuan.file_ttd_kps_url">
                                <p class="text-muted-foreground">Belum ada file terkait dari operator atau KPS.</p>
                            </li>
                        </ul>
                    </div>
                    <form @submit.prevent="submitKpsAction" class="mt-8 pt-6 border-t border-border space-y-6">
                        <h3 class="text-lg font-semibold text-foreground mb-1">Tindakan KPS</h3>
                        
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
                            <Label for="file_tanda_tangan_kps" class="text-foreground">
                                Upload File Transkrip (Sudah Ditandatangani KPS) <span class="text-destructive">*</span>
                            </Label>
                             <div class="mt-1">
                                <label class="w-full flex flex-col items-center px-4 py-6 bg-background border-2 border-dashed border-input text-muted-foreground rounded-lg shadow-sm tracking-wide cursor-pointer hover:border-primary hover:text-primary">
                                    <UploadCloud class="w-8 h-8 mb-2" />
                                    <span class="text-sm leading-normal">{{ kpsActionForm.file_tanda_tangan_kps ? kpsActionForm.file_tanda_tangan_kps.name : 'Pilih atau seret file (PDF)...' }}</span>
                                    <input id="file_tanda_tangan_kps" type="file" @change="handleKpsFileChange" class="hidden" accept=".pdf" />
                                </label>
                            </div>
                            <InputError :message="kpsActionForm.errors.file_tanda_tangan_kps" class="mt-1 text-destructive" />
                        </div>
                        
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="kpsActionForm.processing" class="bg-primary hover:bg-primary/90 text-primary-foreground">
                                <Save class="h-4 w-4 mr-2" />
                                Simpan Keputusan & Update Status
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
/* Tambahkan style untuk textarea jika perlu */
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