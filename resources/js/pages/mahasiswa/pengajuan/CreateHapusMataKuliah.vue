<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { defineProps, computed, ref } from 'vue'; // Tambahkan ref
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { ArrowLeft, Send, UserCircle, Mail, NotebookText, GraduationCap, Trash2, Paperclip, Search } from 'lucide-vue-next'; // Tambah Search

interface MataKuliahOption {
    id: number;
    kode_mk: string;
    nama_mk: string;
    sks: number;
}

interface AuthUser {
    id: number;
    nama_lengkap?: string;
    nama?: string;
    nim?: string;
    email: string;
    program_studi?: string;
}

interface RadioOption {
    value: string;
    label: string;
}

const page = usePage();
const user = computed(() => page.props.auth?.user as AuthUser | undefined);

const props = defineProps<{
    mataKuliahOptions: MataKuliahOption[];
}>();

const form = useForm({
    jenis_pengajuan: 'hapus_mata_kuliah',
    mata_kuliah_ids: [] as number[],
    sks_lulus_total: '',
    sks_lulus_wajib: '',
    sks_lulus_pilihan: '',
    perihal: 'Pendaftaran Semhas dan Sidang Skripsi', // Tambahkan kembali perihal dengan default
    file_pendukung: null as File | null,
});

const perihalOptions: RadioOption[] = [
    { value: 'Pendaftaran Semhas dan Sidang Skripsi', label: 'Pendaftaran Semhas dan Sidang Skripsi' },
    { value: 'Pendaftaran Ujian Khusus', label: 'Pendaftaran Ujian Khusus' },
];

const searchTermMataKuliah = ref(''); // State untuk search mata kuliah

const filteredMataKuliahOptions = computed(() => {
    if (!searchTermMataKuliah.value) {
        return props.mataKuliahOptions;
    }
    const lowerSearchTerm = searchTermMataKuliah.value.toLowerCase();
    return props.mataKuliahOptions.filter(mk => 
        mk.nama_mk.toLowerCase().includes(lowerSearchTerm) ||
        mk.kode_mk.toLowerCase().includes(lowerSearchTerm)
    );
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file_pendukung = target.files[0];
    } else {
        form.file_pendukung = null;
    }
};

const submit = () => {
    form.post(route('pengajuan.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            searchTermMataKuliah.value = ''; // Reset search term juga
        },
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Daftar Pengajuan Hapus MK', href: route('pengajuan.hapus-mk.index') },
    { title: 'Buat Pengajuan Hapus Mata Kuliah', href: route('pengajuan.hapus-mk.create'), isCurrent: true },
];

const toggleMataKuliah = (mkId: number) => {
    console.log('Fungsi toggleMataKuliah dipanggil dengan MK ID:', mkId); // LOG A
    const index = form.mata_kuliah_ids.indexOf(mkId);
    if (index === -1) {
        form.mata_kuliah_ids.push(mkId);
        console.log(mkId, 'ditambahkan. Array sekarang:', JSON.stringify(form.mata_kuliah_ids)); // LOG B
    } else {
        form.mata_kuliah_ids.splice(index, 1);
        console.log(mkId, 'dihapus. Array sekarang:', JSON.stringify(form.mata_kuliah_ids)); // LOG C
    }
};
</script>

<template>
    <Head title="Buat Pengajuan Hapus Mata Kuliah" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">
                        Formulir Pengajuan Hapus Mata Kuliah
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Pilih mata kuliah yang ingin dihapus dan lengkapi informasi yang diperlukan.
                    </p>
                </div>
                <Link :href="route('pengajuan.hapus-mk.index')">
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
                    <input type="hidden" v-model="form.jenis_pengajuan" />
                    <div>
                        <Label class="text-foreground block mb-2">Perihal Pengajuan <span class="text-destructive">*</span></Label>
                        <div class="flex flex-col space-y-2 mt-1">
                           <label v-for="option in perihalOptions" :key="option.value"
                                   class="flex items-center px-4 py-2 border rounded-full cursor-pointer transition-colors duration-150 ease-in-out text-sm w-full sm:w-auto sm:max-w-md"
                                   :class="{ 
                                       'bg-primary text-primary-foreground border-primary ring-2 ring-primary ring-offset-2 ring-offset-background dark:ring-offset-card': form.perihal === option.value, 
                                       'bg-card hover:bg-accent/50 border-border text-foreground': form.perihal !== option.value 
                                   }">
                                <input 
                                    type="radio" 
                                    name="perihal"
                                    :id="'perihal-' + option.value.replace(/\s+/g, '-').toLowerCase()"
                                    :value="option.value" 
                                    v-model="form.perihal"
                                    class="sr-only"
                                    required
                                >
                                <span class="font-medium">{{ option.label }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.perihal" class="mt-1 text-destructive" />
                    </div>

                    <div>
                        <Label class="text-foreground block mb-2">Pilih Mata Kuliah yang Akan Dihapus <span class="text-destructive">*</span></Label>
                        <div class="relative mb-3">
                            <Input 
                                type="text"
                                v-model="searchTermMataKuliah"
                                placeholder="Cari mata kuliah (nama atau kode)..."
                                class="w-full bg-background border-input text-foreground focus:border-primary focus:ring-primary pl-10"
                            />
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        </div>

                        <div v-if="filteredMataKuliahOptions.length > 0" class="mt-2 space-y-3 max-h-60 overflow-y-auto border border-input p-4 rounded-md bg-background">
                            <div v-for="mk in filteredMataKuliahOptions" :key="mk.id" 
                                class="flex items-center p-3 rounded-md hover:bg-accent/50 transition-colors">
                                <input 
                                    type="checkbox"
                                    :id="'mk-' + mk.id"
                                    :value="mk.id"
                                    :checked="form.mata_kuliah_ids.includes(mk.id)"
                                    @change="() => toggleMataKuliah(mk.id)"
                                    class="mr-3 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-primary" 
                                />
                                <Label :for="'mk-' + mk.id" class="flex flex-col cursor-pointer">
                                    <span class="font-medium text-foreground">{{ mk.nama_mk }} ({{ mk.kode_mk }})</span>
                                    <span class="text-xs text-muted-foreground">{{ mk.sks }} SKS</span>
                                </Label>
                            </div>
                        </div>
                        <p v-else-if="searchTermMataKuliah && filteredMataKuliahOptions.length === 0" class="text-sm text-muted-foreground mt-2">
                            Mata kuliah tidak ditemukan dengan kata kunci "{{ searchTermMataKuliah }}".
                        </p>
                        <p v-else-if="!props.mataKuliahOptions || props.mataKuliahOptions.length === 0" class="text-sm text-muted-foreground mt-2">
                            Tidak ada mata kuliah yang tersedia untuk dipilih. Harap hubungi operator akademik.
                        </p>
                        <InputError :message="form.errors.mata_kuliah_ids" class="mt-1 text-destructive" />
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <Label for="sks_lulus_total" class="text-foreground">Jumlah SKS Lulus Total</Label>
                            <Input id="sks_lulus_total" type="number" v-model="form.sks_lulus_total" class="mt-1 block w-full bg-background border-input text-foreground focus:border-primary focus:ring-primary" placeholder="Contoh: 100" />
                            <InputError :message="form.errors.sks_lulus_total" class="mt-1 text-destructive" />
                        </div>
                        <div>
                            <Label for="sks_lulus_wajib" class="text-foreground">SKS Lulus Wajib</Label>
                            <Input id="sks_lulus_wajib" type="number" v-model="form.sks_lulus_wajib" class="mt-1 block w-full bg-background border-input text-foreground focus:border-primary focus:ring-primary" placeholder="Contoh: 80" />
                            <InputError :message="form.errors.sks_lulus_wajib" class="mt-1 text-destructive" />
                        </div>
                        <div>
                            <Label for="sks_lulus_pilihan" class="text-foreground">SKS Lulus Pilihan</Label>
                            <Input id="sks_lulus_pilihan" type="number" v-model="form.sks_lulus_pilihan" class="mt-1 block w-full bg-background border-input text-foreground focus:border-primary focus:ring-primary" placeholder="Contoh: 20" />
                            <InputError :message="form.errors.sks_lulus_pilihan" class="mt-1 text-destructive" />
                        </div>
                    </div>
                    
                    <div>
                        <Label for="file_pendukung" class="text-foreground">File Pendukung (Opsional, PDF/JPG/PNG/DOCX, maks 2MB)</Label>
                        <div class="mt-1">
                            <label class="w-full flex flex-col items-center px-4 py-3 bg-background border border-input text-primary rounded-lg shadow-sm tracking-wide uppercase cursor-pointer hover:bg-accent hover:text-accent-foreground">
                                <Paperclip class="w-5 h-5" />
                                <span class="mt-1 text-xs leading-normal">{{ form.file_pendukung ? form.file_pendukung.name : 'Pilih file...' }}</span>
                                <input id="file_pendukung" type="file" @change="handleFileChange" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
                            </label>
                        </div>
                         <InputError :message="form.errors.file_pendukung" class="mt-1 text-destructive" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <Button type="submit" :disabled="form.processing || form.mata_kuliah_ids.length === 0" class="bg-primary hover:bg-primary/90 text-primary-foreground">
                            <Trash2 class="h-4 w-4 mr-2" />
                            Ajukan Penghapusan
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>