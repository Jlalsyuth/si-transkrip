<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue'; // defineProps tidak lagi dibutuhkan untuk keperluanOptions
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { ArrowLeft, Send, UserCircle, Mail, NotebookText, GraduationCap } from 'lucide-vue-next';

// Interface KeperluanOption tidak lagi dibutuhkan dari props, tapi bisa digunakan untuk struktur data lokal
interface RadioOption {
    value: string;
    label: string;
}

interface AuthUser {
    id: number;
    nama_lengkap?: string;
    nama?: string;
    nim?: string;
    email: string;
    program_studi?: string;
}

const page = usePage();
const user = computed(() => page.props.auth?.user as AuthUser | undefined);

// props keperluanPengajuanOptions dihapus
// const props = defineProps<{
//     keperluanPengajuanOptions?: KeperluanOption[];
// }>();

const form = useForm({
    jenis_pengajuan: 'transkrip_sementara',
    keperluan_transkrip: 'Beasiswa', // Field diubah dan diberi default value
    bahasa_transkrip: 'indonesia',
});

const bahasaOptions: RadioOption[] = [
    { value: 'indonesia', label: 'Indonesia' },
    { value: 'inggris', label: 'Inggris' },
];

// Definisikan opsi keperluan secara statis di sini
const keperluanOptions: RadioOption[] = [
    { value: 'Beasiswa', label: 'Beasiswa' },
    { value: 'Konseling ke Kemahasiswaan', label: 'Konseling ke Kemahasiswaan' },
    { value: 'PKL', label: 'PKL' },
    { value: 'Pendaftaran Seminar Hasil', label: 'Pendaftaran Seminar Hasil' },
    { value: 'Konsultasi DPA untuk mahasiswa yang memprogram KRS Tugas Akhir/Skripsi', label: 'Konsultasi DPA untuk mahasiswa yang memprogram KRS Tugas Akhir/Skripsi' },        
    { value: 'Melamar Pekerjaan', label: 'Melamar Pekerjaan' },
    { value: 'Lainnya', label: 'Lainnya' },
    // Anda bisa menambahkan opsi "Pilih keperluan..." jika ingin ada default yang tidak valid
    // { value: '', label: 'Pilih keperluan...', disabled: true }, 
    // Tapi untuk radio group, lebih umum jika salah satu sudah terpilih secara default.
];

const submit = () => {
    form.post(route('pengajuan.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset(); // Akan mereset ke nilai default di useForm
        },
        onError: (errors) => {
            // form.errors akan otomatis terisi.
        }
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Daftar Pengajuan Saya', href: route('pengajuan.index') },
    { title: 'Buat Pengajuan Transkrip', href: route('pengajuan.create'), isCurrent: true },
];

</script>

<template>
    <Head title="Buat Pengajuan Transkrip" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">
                        Formulir Pengajuan Transkrip Sementara
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Pastikan data Anda benar dan lengkapi informasi di bawah ini.
                    </p>
                </div>
                <Link :href="route('pengajuan.index')">
                    <Button variant="outline" class="border-border text-foreground hover:bg-accent hover:text-accent-foreground w-full sm:w-auto">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Kembali ke Daftar
                    </Button>
                </Link>
            </div>

            <div v-if="user" class="bg-card text-card-foreground shadow-md rounded-lg p-6 border border-border">
                <h2 class="text-lg font-semibold text-foreground mb-4">Informasi Mahasiswa</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <Label class="text-muted-foreground flex items-center"><UserCircle class="h-4 w-4 mr-2 shrink-0"/>Nama Lengkap</Label>
                        <p class="text-foreground font-medium mt-0.5">{{ user.nama_lengkap || user.nama || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground flex items-center"><NotebookText class="h-4 w-4 mr-2 shrink-0"/>NIM</Label>
                        <p class="text-foreground font-medium mt-0.5">{{ user.nim || '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground flex items-center"><GraduationCap class="h-4 w-4 mr-2 shrink-0"/>Program Studi</Label>
                        <p class="text-foreground font-medium mt-0.5">{{ user.program_studi ? user.program_studi.replace(/_/g, ' ') : '-' }}</p>
                    </div>
                    <div>
                        <Label class="text-muted-foreground flex items-center"><Mail class="h-4 w-4 mr-2 shrink-0"/>Email</Label>
                        <p class="text-foreground font-medium mt-0.5">{{ user.email || '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-card text-card-foreground shadow-md rounded-lg p-6 md:p-8 border border-border">
                <form @submit.prevent="submit" class="space-y-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <Label class="text-foreground block mb-2">Keperluan Pengajuan <span class="text-destructive">*</span></Label>
                            <div class="flex flex-col space-y-2 mt-1"> 
                                <label v-for="option in keperluanOptions" :key="option.value" 
                                       class="flex items-center px-4 py-2 border rounded-full cursor-pointer transition-colors duration-150 ease-in-out text-sm w-full"
                                       :class="{ 
                                           'bg-primary text-primary-foreground border-primary ring-2 ring-primary ring-offset-2 ring-offset-background dark:ring-offset-card': form.keperluan_transkrip === option.value, 
                                           'bg-card hover:bg-accent/50 border-border text-foreground': form.keperluan_transkrip !== option.value 
                                       }">
                                    <input 
                                        type="radio" 
                                        name="keperluan_transkrip"
                                        :id="'keperluan-' + option.value"
                                        :value="option.value" 
                                        v-model="form.keperluan_transkrip"
                                        class="sr-only"
                                        required 
                                    >
                                    <span class="font-medium">{{ option.label }}</span>
                                </label>
                            </div>
                            <InputError :message="form.errors.keperluan_transkrip" class="mt-1 text-destructive" />
                        </div>


                        <div>
                            <Label class="text-foreground block mb-2">Bahasa Transkrip <span class="text-destructive">*</span></Label>
                            <div class="flex flex-col space-y-2 mt-1"> 
                               <label v-for="option in bahasaOptions" :key="option.value"
                                       class="flex items-center px-4 py-2 border rounded-full cursor-pointer transition-colors duration-150 ease-in-out text-sm w-full"
                                       :class="{ 
                                           'bg-primary text-primary-foreground border-primary ring-2 ring-primary ring-offset-2 ring-offset-background dark:ring-offset-card': form.bahasa_transkrip === option.value, 
                                           'bg-card hover:bg-accent/50 border-border text-foreground': form.bahasa_transkrip !== option.value 
                                       }">
                                    <input 
                                        type="radio" 
                                        name="bahasa_transkrip"
                                        :id="'bahasa-' + option.value"
                                        :value="option.value" 
                                        v-model="form.bahasa_transkrip"
                                        class="sr-only"
                                    >
                                    <span class="font-medium">{{ option.label }}</span>
                                </label>
                            </div>
                            <InputError :message="form.errors.bahasa_transkrip" class="mt-1 text-destructive" />
                        </div>
                    </div>


                    <div class="flex items-center justify-end mt-6">
                        <Button type="submit" :disabled="form.processing" class="bg-primary hover:bg-primary/90 text-primary-foreground">
                            <Send class="h-4 w-4 mr-2" />
                            Kirim Pengajuan
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Tidak ada style scoped tambahan yang diperlukan jika Tailwind sudah cukup */
</style>