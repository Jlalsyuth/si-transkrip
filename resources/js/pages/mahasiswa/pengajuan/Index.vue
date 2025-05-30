<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import { Button } from '@/components/ui/button';
import { PlusCircle, ListFilter, ArrowRight } from 'lucide-vue-next';

interface StatusPengajuan {
    id: number;
    nama_status_display: string;
    kode_status?: string;
}

// Interface KeperluanPengajuan tidak lagi digunakan di sini jika keperluan adalah string
// interface KeperluanPengajuan {
//     id: number;
//     nama_keperluan: string;
// }

interface Pengajuan {
    id: number;
    keperluan_transkrip?: string | null; // <<--- PERUBAHAN DI SINI: sekarang string
    jenis_pengajuan: string;
    tanggal_pengajuan: string;
    current_status: StatusPengajuan;
}

interface PaginatedData<T> {
    data: T[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page: number;
}

const props = defineProps<{
    pengajuans: PaginatedData<Pengajuan>;
    pageTitle: string;
}>();

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: props.pageTitle,
        href: route('pengajuan.index'),
        isCurrent: true,
    },
];

// Fungsi formatKeperluan sekarang lebih sederhana jika hanya menangani string
const formatKeperluan = (keperluan?: string | null) => {
    return keperluan || '-'; // Jika string, tampilkan langsung atau '-' jika null/kosong
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
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ pageTitle }}</h1>
                    <p class="text-sm text-muted-foreground">Yuk, ajukan transkrip sementaramu di sini jika diperlukan.</p>
                </div>
            </div>

            <div class="bg-card text-card-foreground shadow-md rounded-lg p-6 border border-border">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Pengajuan Terbaru</h2>
                        <p class="text-sm text-muted-foreground">Lihat pengajuan transkrip sementara yang baru saja diajukan.</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('pengajuan.create')"> 
                            <Button class="bg-primary hover:bg-primary/90 text-primary-foreground">
                                <PlusCircle class="h-4 w-4 mr-2" />
                                Ajukan Transkrip Baru
                            </Button>
                        </Link>
                        <Button variant="outline" class="border-border text-foreground hover:bg-accent hover:text-accent-foreground">
                            <ListFilter class="h-4 w-4 mr-2" />
                            Filter
                        </Button>
                    </div>
                </div>

                <div v-if="pengajuans.data.length === 0" class="text-center py-10">
                    <p class="text-muted-foreground text-lg">Anda belum memiliki pengajuan transkrip.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    No.
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    Keperluan Pengajuan
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    Tanggal Pengajuan
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            <tr v-for="(pengajuan, index) in pengajuans.data" :key="pengajuan.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">
                                    {{ pengajuans.from + index }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-foreground">
                                    {{ formatKeperluan(pengajuan.keperluan_transkrip) }}
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
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('pengajuan.show', pengajuan.id)" class="text-primary hover:text-primary/80 flex items-center justify-end gap-1">
                                        Lihat Detail <ArrowRight class="h-3 w-3" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="pengajuans.data.length > 0 && pengajuans.links.length > 3" class="mt-6 flex flex-col sm:flex-row justify-between items-center text-sm text-muted-foreground">
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