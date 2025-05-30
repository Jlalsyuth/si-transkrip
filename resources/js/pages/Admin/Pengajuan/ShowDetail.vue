<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { defineProps, computed, ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    ArrowLeft, Paperclip, ListCollapse, UserCircle, CalendarDays,
    MessageSquareText, Mail, NotebookText, GraduationCap, Save, UploadCloud
} from 'lucide-vue-next';

// Interface User, StatusPengajuan, dll. (pastikan sudah sesuai)
interface User {
    id: number;
    nama_lengkap?: string;
    nama?: string;
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
    keperluan_transkrip?: string | null;
    bahasa_transkrip?: 'indonesia' | 'inggris' | null;
    perihal?: string | null;
    tanggal_pengajuan: string;
    current_status: StatusPengajuan;
    catatan_mahasiswa?: string;
    catatan_operator?: string;
    catatan_kps?: string;
    file_transkrip_sementara_unggah_mhs_url?: string;
    file_transkrip_final_operator_url?: string;
    file_ttd_kps_url?: string; // <-- [TAMBAHKAN] URL untuk file TTD KPS yang sudah diunggah
    detail_hapus_mks?: DetailHapusMkItem[];
    sks_lulus_total?: string | number | null;
    sks_lulus_wajib?: string | number | null;
    sks_lulus_pilihan?: string | number | null;
    tracking_pengajuans: TrackingPengajuan[];
    created_at: string;
}


const props = defineProps<{
    pengajuan: Pengajuan;
    pageTitle: string;
    statusOptions: Array<{ id: number; nama_status_display: string }>;
}>();

const adminActionForm = useForm({
    new_status_id: props.pengajuan.current_status.id,
    catatan_operator: props.pengajuan.catatan_operator || '',
    catatan_kps: props.pengajuan.catatan_kps || '',
    file_final_operator_baru: null as File | null,
    file_ttd_kps_baru: null as File | null, // <-- [TAMBAHKAN] Field untuk unggah file TTD KPS baru
});

const breadcrumbs = [
    { title: 'Admin Dashboard', href: route('admin.dashboard') },
    { title: 'Daftar Pengajuan', href: route(props.pengajuan.jenis_pengajuan === 'transkrip_sementara' ? 'admin.pengajuan.transkrip.index' : 'admin.pengajuan.hapus-mk.index') }, // Lebih dinamis
    { title: props.pageTitle, href: route('admin.pengajuan.show', props.pengajuan.id), isCurrent: true },
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

const getPelakuName = (pelaku: User | { nama: string } | undefined) => {
    if (!pelaku) return 'Sistem';
    return pelaku.nama || (pelaku as User).nama_lengkap || 'User Tidak Dikenal';
}

const formatProgramStudi = (prodi?: string) => {
    return prodi ? prodi.replace(/_/g, ' ') : '-';
}

const handleFileFinalAdminChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        adminActionForm.file_final_operator_baru = target.files[0];
    } else {
        adminActionForm.file_final_operator_baru = null;
    }
};

// [TAMBAHKAN] Handler untuk file TTD KPS
const handleFileTtdKpsChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        adminActionForm.file_ttd_kps_baru = target.files[0];
    } else {
        adminActionForm.file_ttd_kps_baru = null;
    }
};

const submitAdminActions = () => {
    adminActionForm.post(route('admin.pengajuan.update', props.pengajuan.id), {
        preserveScroll: true,
        onSuccess: () => {
            adminActionForm.file_final_operator_baru = null;
            adminActionForm.file_ttd_kps_baru = null; // <-- [TAMBAHKAN] Reset file TTD KPS setelah submit
            // Jika Anda ingin data pengajuan di-refresh otomatis setelah update untuk menampilkan link file baru:
            // import { router } from '@inertiajs/vue3'; // Tambahkan ini di atas jika belum
            // router.reload({ only: ['pengajuan'] }); // atau usePage().props.pengajuan = newPengajuanData dari controller
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
                    <h1 class="text-2xl font-semibold text-foreground">
                        {{ pageTitle }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Jenis: {{ formatJenisPengajuan(pengajuan.jenis_pengajuan) }} oleh {{ pengajuan.mahasiswa?.nama_lengkap || pengajuan.mahasiswa?.nama }} ({{ pengajuan.mahasiswa?.nim }})
                    </p>
                </div>
                <Link :href="route(pengajuan.jenis_pengajuan === 'transkrip_sementara' ? 'admin.pengajuan.transkrip.index' : 'admin.pengajuan.hapus-mk.index')">
                    <Button variant="outline" class="border-border text-foreground hover:bg-accent hover:text-accent-foreground">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Kembali ke Daftar
                    </Button>
                </Link>
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-card text-card-foreground shadow-md rounded-lg p-6 border border-border">
                     <div class="sm:col-span-2 mb-4 pb-4 border-b border-border">
                        <h3 class="text-md font-semibold text-foreground mb-2">Data Mahasiswa</h3>
                        <p><strong class="text-muted-foreground w-28 inline-block">Nama:</strong> {{ pengajuan.mahasiswa?.nama_lengkap || pengajuan.mahasiswa?.nama || '-' }}</p>
                        <p><strong class="text-muted-foreground w-28 inline-block">NIM:</strong> {{ pengajuan.mahasiswa?.nim || '-' }}</p>
                        <p><strong class="text-muted-foreground w-28 inline-block">Prodi:</strong> {{ formatProgramStudi(pengajuan.mahasiswa?.program_studi) }}</p>
                        <p><strong class="text-muted-foreground w-28 inline-block">Email:</strong> {{ pengajuan.mahasiswa?.email || '-' }}</p>
                    </div>

                    <h3 class="text-md font-semibold text-foreground mb-2 sm:col-span-2">Detail Pengajuan</h3>
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
                                <p class="font-medium">{{ pengajuan.keperluan_transkrip || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Bahasa Transkrip:</p>
                                <p class="font-medium">{{ pengajuan.bahasa_transkrip ? pengajuan.bahasa_transkrip.charAt(0).toUpperCase() + pengajuan.bahasa_transkrip.slice(1) : '-' }}</p>
                            </div>
                        </template>
                        <template v-if="pengajuan.jenis_pengajuan === 'hapus_mata_kuliah'">
                             <div>
                                <p class="text-muted-foreground">Perihal:</p>
                                <p class="font-medium">{{ pengajuan.perihal || '-' }}</p>
                            </div>
                           <div class="sm:col-span-2">
                                <p class="text-muted-foreground">SKS Lulus (Total/Wajib/Pilihan):</p>
                                <p class="font-medium">
                                    {{ pengajuan.sks_lulus_total ?? '-' }} /
                                    {{ pengajuan.sks_lulus_wajib ?? '-' }} /
                                    {{ pengajuan.sks_lulus_pilihan ?? '-' }}
                                </p>
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
                                    File dari Mahasiswa
                                    <span v-if="pengajuan.jenis_pengajuan === 'transkrip_sementara'">(Pendukung Transkrip)</span>
                                    <span v-else-if="pengajuan.jenis_pengajuan === 'hapus_mata_kuliah'">(Pendukung Hapus MK)</span>
                                </a>
                            </li>
                            <li v-if="pengajuan.file_transkrip_final_operator_url">
                                <a :href="pengajuan.file_transkrip_final_operator_url" target="_blank" class="text-primary hover:text-primary/80 hover:underline flex items-center">
                                    <Paperclip class="h-4 w-4 mr-2 shrink-0" /> Transkrip Final dari Operator
                                </a>
                            </li>
                            <li v-if="!pengajuan.file_transkrip_sementara_unggah_mhs_url && !pengajuan.file_transkrip_final_operator_url">
                                <p class="text-muted-foreground">Tidak ada file terkait.</p>
                            </li>
                        </ul>
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

                    <form @submit.prevent="submitAdminActions" class="mt-6 pt-4 border-t border-border space-y-6">
                        <h3 class="text-md font-semibold text-foreground mb-2">Aksi Admin/Operator</h3>

                        <div>
                            <Label for="new_status_id" class="text-foreground">Ubah Status Pengajuan</Label>
                            <select
                                id="new_status_id"
                                v-model="adminActionForm.new_status_id"
                                class="mt-1 block w-full px-3 py-2 bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-ring focus:border-primary text-sm text-foreground"
                            >
                                <option v-for="status in props.statusOptions" :key="status.id" :value="status.id">
                                    {{ status.nama_status_display }}
                                </option>
                            </select>
                            <InputError :message="adminActionForm.errors.new_status_id" class="mt-1 text-destructive" />
                        </div>

                        <div>
                            <Label for="catatan_operator_admin" class="text-foreground">Catatan Operator</Label>
                            <textarea
                                id="catatan_operator_admin"
                                v-model="adminActionForm.catatan_operator"
                                rows="3"
                                class="mt-1 block w-full px-3 py-2 bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-ring focus:border-primary text-sm text-foreground"
                                placeholder="Tambahkan catatan operator..."
                            ></textarea>
                            <InputError :message="adminActionForm.errors.catatan_operator" class="mt-1 text-destructive" />
                        </div>

                        <div>
                            <Label for="catatan_kps_admin" class="text-foreground">Catatan untuk KPS (Opsional)</Label>
                            <textarea
                                id="catatan_kps_admin"
                                v-model="adminActionForm.catatan_kps"
                                rows="3"
                                class="mt-1 block w-full px-3 py-2 bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-ring focus:border-primary text-sm text-foreground"
                                placeholder="Tambahkan catatan yang akan dilihat KPS..."
                            ></textarea>
                            <InputError :message="adminActionForm.errors.catatan_kps" class="mt-1 text-destructive" />
                        </div>

                                                <div>
                            <Label for="file_ttd_kps_baru" class="text-foreground">
                                Upload File TTD KPS (Opsional)
                            </Label>
                            <div class="mt-1">
                                <label class="w-full flex flex-col items-center px-4 py-3 bg-background border-2 border-dashed border-input text-muted-foreground rounded-lg shadow-sm tracking-wide cursor-pointer hover:border-primary hover:text-primary">
                                    <UploadCloud class="w-8 h-8" />
                                    <span class="mt-1 text-sm leading-normal">{{ adminActionForm.file_ttd_kps_baru ? adminActionForm.file_ttd_kps_baru.name : 'Pilih atau seret file (PDF, PNG, JPG)...' }}</span>
                                    <input id="file_ttd_kps_baru" type="file" @change="handleFileTtdKpsChange" class="hidden" accept=".pdf,.png,.jpg,.jpeg" />
                                </label>
                            </div>
                            <InputError :message="adminActionForm.errors.file_ttd_kps_baru" class="mt-1 text-destructive" />
                        </div>
                                                <div v-if="pengajuan.jenis_pengajuan === 'transkrip_sementara' || pengajuan.jenis_pengajuan === 'hapus_mata_kuliah'">
                            <Label for="file_final_operator_baru" class="text-foreground">
                                                                Upload File Final dari Operator (mis. Transkrip Final)
                            </Label>
                            <div class="mt-1">
                                <label class="w-full flex flex-col items-center px-4 py-3 bg-background border-2 border-dashed border-input text-muted-foreground rounded-lg shadow-sm tracking-wide cursor-pointer hover:border-primary hover:text-primary">
                                    <UploadCloud class="w-8 h-8" />
                                    <span class="mt-1 text-sm leading-normal">{{ adminActionForm.file_final_operator_baru ? adminActionForm.file_final_operator_baru.name : 'Pilih atau seret file (PDF)...' }}</span>
                                    <input id="file_final_operator_baru" type="file" @change="handleFileFinalAdminChange" class="hidden" accept=".pdf" />
                                </label>
                            </div>
                            <InputError :message="adminActionForm.errors.file_final_operator_baru" class="mt-1 text-destructive" />
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="adminActionForm.processing" class="bg-primary hover:bg-primary/90 text-primary-foreground">
                                <Save class="h-4 w-4 mr-2" />
                                Simpan Perubahan & Update Status
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

<style scoped>
/* Style untuk textarea bisa ditambahkan di sini jika perlu agar konsisten */
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