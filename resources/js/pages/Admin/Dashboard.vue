<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, defineProps, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button'; // Untuk tombol Edit Profil
import {
    FileText, FileMinus2, AlertCircle, Clock, UserCircle, Edit3, Mail, // Menambahkan Mail untuk profil admin
    BarChartBig, PieChartIcon, // Ikon placeholder grafik
    // Lucide Icons untuk Sidebar (jika layout ini punya sidebar sendiri)
    // LayoutDashboard, LogOut, Users, Database // Ini lebih cocok di komponen layout sidebar
} from 'lucide-vue-next';
import { Bar, Pie } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    Colors // Opsional, jika ingin skema warna default Chart.js
} from 'chart.js';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    Colors // Daftarkan Colors jika digunakan
);
// --- Interfaces ---
interface AuthUser {
    id: number;
    nama: string; // Menggunakan 'nama' sesuai database Anda
    nim?: string; 
    email: string;
    program_studi?: string;
    profile_photo_url?: string;
    role?: string;
}
interface StatusInfo {
    id: number;
    nama_status_display: string;
    kode_status?: string;
}
interface StatItem {
    current_status_id: number;
    total: number;
    current_status: StatusInfo;
}
interface MahasiswaInfo { // Untuk data mahasiswa di pengajuanPenting & di tabel admin
    id: number;
    nama: string; // Menggunakan 'nama'
    nim: string;
    program_studi?: string; // Tambahkan jika ingin ditampilkan di tabel pengajuan penting
}
interface PengajuanPenting {
    id: number;
    mahasiswa: MahasiswaInfo;
    jenis_pengajuan: string;
    tanggal_pengajuan: string;
    current_status: StatusInfo;
    perihal?: string;
}
interface PengajuanBulanan {
    tahun: number;
    bulan_angka: number;
    nama_bulan: string;
    label_bulan_tahun: string;
    total_pengajuan: number;
}

const page = usePage();
const adminUser = computed(() => page.props.auth?.user as AuthUser | undefined);

const props = defineProps<{
    statsTranskrip?: StatItem[];
    statsHapusMk?: StatItem[];
    pengajuanPenting?: PengajuanPenting[];
    pengajuanPerBulan?: PengajuanBulanan[];
    pageTitle?: string;
}>();

const breadcrumbs = [
    { title: 'Admin Dashboard', href: route('admin.dashboard'), isCurrent: true },
];

// ... (Fungsi helper formatDate, getStatusClass, formatJenisPengajuan TETAP SAMA) ...
const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const options: Intl.DateTimeFormatOptions = { day: '2-digit', month: 'long', year: 'numeric' };
    try { return new Date(dateString).toLocaleDateString('id-ID', options); } 
    catch (e) { return "Invalid Date"; }
};
const formatJenisPengajuan = (jenis: string) => {
    if (jenis === 'transkrip_sementara') return 'Transkrip';
    if (jenis === 'hapus_mata_kuliah') return 'Hapus MK';
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


const barChartData = computed(() => {
    const labels = props.pengajuanPerBulan?.map(item => item.label_bulan_tahun) || [];
    const data = props.pengajuanPerBulan?.map(item => item.total_pengajuan) || [];
    return {
        labels,
        datasets: [{
            label: 'Total Pengajuan',
            data,
            // Contoh penggunaan warna dari variabel CSS (membutuhkan setup variabel HSL terpisah di app.css)
            backgroundColor: `hsla(${getComputedStyle(document.documentElement).getPropertyValue('--primary-h') || 25}, ${getComputedStyle(document.documentElement).getPropertyValue('--primary-s') || '90%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--primary-l') || '55%'}, 0.7)`,
            borderColor: `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--primary-h') || 25}, ${getComputedStyle(document.documentElement).getPropertyValue('--primary-s') || '90%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--primary-l') || '55%'})`,
            borderWidth: 1,
            borderRadius: 4, // Untuk membuat bar agak rounded
        }]
    };
});

const pieChartData = computed(() => {
    // Menggabungkan statistik dari kedua jenis pengajuan untuk pie chart status
    const allStats = [...(props.statsTranskrip || []), ...(props.statsHapusMk || [])];
    const aggregatedStats: { [key: string]: number } = {};
    
    allStats.forEach(stat => {
        // Pastikan stat.current_status tidak null/undefined
        const statusName = stat.current_status?.nama_status_display || 'Status Tidak Diketahui';
        aggregatedStats[statusName] = (aggregatedStats[statusName] || 0) + stat.total;
    });

    const labels = Object.keys(aggregatedStats);
    const data = Object.values(aggregatedStats);
    
    // Contoh palet warna (Anda bisa kembangkan atau ambil dari tema)
    // Warna ini akan digunakan jika ChartJS.register(Colors) tidak dipakai atau ingin override
    const backgroundColors = [
        `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--primary-h') || 25}, ${getComputedStyle(document.documentElement).getPropertyValue('--primary-s') || '90%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--primary-l') || '55%'})`, // Oranye (Primary)
        `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--accent-h') || 25}, ${getComputedStyle(document.documentElement).getPropertyValue('--accent-s') || '95%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--accent-l') || '90%'})`, // Oranye Muda (Accent)
        'hsl(140, 60%, 60%)', // Contoh Hijau
        'hsl(50, 80%, 60%)',  // Contoh Kuning
        `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--destructive-h') || 0}, ${getComputedStyle(document.documentElement).getPropertyValue('--destructive-s') || '84.2%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--destructive-l') || '60.2%'})`, // Merah (Destructive)
        'hsl(210, 70%, 60%)', // Contoh Biru
        `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--secondary-h') || 240}, ${getComputedStyle(document.documentElement).getPropertyValue('--secondary-s') || '4.8%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--secondary-l') || '95.9%'})`, // Abu-abu (Secondary)
        // Tambahkan lebih banyak warna jika statusnya banyak
    ];

    return {
        labels,
        datasets: [{
            label: 'Distribusi Status Pengajuan',
            data,
            backgroundColor: backgroundColors.slice(0, data.length),
            hoverOffset: 8,
            borderWidth: 1, // Tambahkan border tipis antar slice
            borderColor: `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--background-h') || 0}, ${getComputedStyle(document.documentElement).getPropertyValue('--background-s') || '0%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--background-l') || '98%'})`, // Border warna background
        }]
    };
});

// Opsi umum untuk Chart.js
const commonChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                // Mengambil warna teks dari variabel CSS tema
                color: `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--foreground-h') || 240}, ${getComputedStyle(document.documentElement).getPropertyValue('--foreground-s') || '10%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--foreground-l') || '3.9%'})`,
                padding: 15,
                font: {
                    size: 11,
                    family: "'Instrument Sans', sans-serif" // Sesuaikan dengan font-sans Anda
                }
            }
        },
        tooltip: {
            // Kustomisasi tooltip jika perlu
            titleFont: {
                family: "'Instrument Sans', sans-serif"
            },
            bodyFont: {
                family: "'Instrument Sans', sans-serif"
            }
        }
    },
};

// Opsi spesifik untuk Bar Chart
const chartOptions = {
    ...commonChartOptions,
    scales: {
        y: {
            beginAtZero: true,
            ticks: { 
                color: `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground-h') || 240}, ${getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground-s') || '3.8%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground-l') || '46.1%'})`,
                precision: 0 // Tidak ada desimal untuk jumlah pengajuan
            },
            grid: { 
                color: `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--border-h') || 240}, ${getComputedStyle(document.documentElement).getPropertyValue('--border-s') || '5.9%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--border-l') || '90%'})` 
            }
        },
        x: {
            ticks: { 
                color: `hsl(${getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground-h') || 240}, ${getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground-s') || '3.8%'}, ${getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground-l') || '46.1%'})` 
            },
            grid: { 
                display: false 
            }
        }
    }
};

// Opsi spesifik untuk Pie Chart (legenda mungkin ingin di posisi berbeda)
const pieChartOptions = {
    ...commonChartOptions,
    plugins: {
        ...commonChartOptions.plugins, // Ambil konfigurasi legenda dari common
        legend: {
            ...commonChartOptions.plugins.legend,
            position: 'right' as const, // Override posisi legenda untuk pie chart
            labels: {
                ...commonChartOptions.plugins.legend?.labels,
                boxWidth: 12, // Ukuran kotak warna legenda
            }
        },
    }
};
</script>

<template>
    <Head :title="props.pageTitle || 'Admin Dashboard'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <h1 class="text-3xl font-bold text-foreground">
                Selamat Datang, {{ adminUser?.nama || 'Admin' }}!
                <span class="inline-block origin-[70%_70%] animate-[wave_2.5s_ease-in-out_infinite]">👋</span>
            </h1>
            <p class="text-muted-foreground -mt-4 mb-2">
                Ini adalah ringkasan aktivitas sistem pengajuan.
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="md:col-span-1 bg-card text-card-foreground rounded-xl border border-border shadow-sm p-6 flex flex-col">
                    <h2 class="text-lg font-semibold text-foreground mb-4">Profil Admin</h2>
                    <div class="flex flex-col items-center text-center mb-4">
                        <img v-if="adminUser?.profile_photo_url" :src="adminUser.profile_photo_url" alt="Foto Profil" class="w-24 h-24 rounded-full object-cover mb-3 border-2 border-primary">
                        <UserCircle v-else class="w-24 h-24 text-muted-foreground mb-3" />
                        <p class="text-xl font-semibold text-foreground">{{ adminUser?.nama || 'Nama Admin' }}</p>
                        <p v-if="adminUser?.role" class="mt-1 text-xs bg-accent text-accent-foreground px-2 py-0.5 rounded-full inline-block">{{ adminUser.role.toUpperCase() }}</p>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center">
                            <Mail class="w-4 h-4 mr-2 text-muted-foreground shrink-0" />
                            <span class="text-muted-foreground mr-1">Email:</span>
                            <span class="text-foreground truncate">{{ adminUser?.email }}</span>
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

                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card class="border-border shadow-sm">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">Pengajuan Transkrip</CardTitle>
                            <FileText class="h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-foreground">
                                {{ props.statsTranskrip?.reduce((acc, curr) => acc + curr.total, 0) || 0 }} Total
                            </div>
                            <div class="text-xs text-muted-foreground mt-1 space-y-0.5">
                                <p v-for="stat in props.statsTranskrip" :key="stat.current_status.id">
                                    {{ stat.current_status.nama_status_display }}: {{ stat.total }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card class="border-border shadow-sm">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">Pengajuan Hapus MK</CardTitle>
                            <FileMinus2 class="h-5 w-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-foreground">
                                 {{ props.statsHapusMk?.reduce((acc, curr) => acc + curr.total, 0) || 0 }} Total
                            </div>
                             <div class="text-xs text-muted-foreground mt-1 space-y-0.5">
                                <p v-for="stat in props.statsHapusMk" :key="stat.current_status.id">
                                    {{ stat.current_status.nama_status_display }}: {{ stat.total }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                     <Card class="border-border shadow-sm md:col-span-2">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium text-destructive">Perlu Perhatian Segera</CardTitle>
                            <AlertCircle class="h-5 w-5 text-destructive" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-foreground">
                                 {{ props.pengajuanPenting?.length || 0 }} Pengajuan
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Menunggu diproses atau butuh persetujuan.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <Card class="border-border shadow-sm">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Jumlah Pengajuan per Bulan (1 Tahun Terakhir)</CardTitle>
                        <CardDescription class="text-xs">Total pengajuan transkrip dan hapus mata kuliah.</CardDescription>
                    </CardHeader>
                        <CardContent class="h-[300px] p-2 md:p-4">
                            <Bar v-if="props.pengajuanPerBulan && props.pengajuanPerBulan.length > 0" 
                                :data="barChartData" 
                                :options="chartOptions" />
                            <div v-else class="flex items-center justify-center h-full text-muted-foreground">
                                <BarChartBig class="h-16 w-16 opacity-50 mr-2" /> Data tidak cukup untuk grafik.
                            </div>
                        </CardContent>
                </Card>
                <Card class="border-border shadow-sm">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Distribusi Status Pengajuan (Semua Jenis)</CardTitle>
                        <CardDescription class="text-xs">Persentase pengajuan berdasarkan status saat ini.</CardDescription>
                    </CardHeader>
                    <CardContent class="h-[300px] p-2 md:p-4 flex items-center justify-center">
                        <Pie v-if="pieChartData.datasets[0].data.length > 0" 
                            :data="pieChartData" 
                            :options="pieChartOptions" 
                            class="max-h-[280px] max-w-[280px] sm:max-h-[250px] sm:max-w-[250px]" />
                        <div v-else class="flex items-center justify-center h-full text-muted-foreground">
                            <PieChartIcon class="h-16 w-16 opacity-50 mr-2" /> Data tidak cukup untuk grafik.
                        </div>
                    </CardContent>
                </Card>
            </div>
            <div class="bg-card text-card-foreground shadow-md rounded-lg p-6 border border-border mt-2">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-foreground">Pengajuan Membutuhkan Perhatian</h2>
                </div>
                <div v-if="!props.pengajuanPenting || props.pengajuanPenting.length === 0" class="text-center py-6">
                    <Clock class="mx-auto h-10 w-10 text-muted-foreground mb-2" />
                    <p class="text-muted-foreground">Tidak ada pengajuan yang membutuhkan perhatian segera.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground uppercase">Mahasiswa</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground uppercase">Jenis</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground uppercase">Status</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="item in props.pengajuanPenting" :key="item.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-foreground">{{ item.mahasiswa.nama }}</div>
                                    <div class="text-muted-foreground text-xs">{{ item.mahasiswa.nim }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-foreground">{{ formatJenisPengajuan(item.jenis_pengajuan) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">{{ formatDate(item.tanggal_pengajuan) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 inline-flex leading-4 font-semibold rounded-full text-xs" :class="getStatusClass(item.current_status.kode_status, item.current_status.nama_status_display)">
                                        {{ item.current_status.nama_status_display }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-medium">
                                    <Link :href="route('admin.pengajuan.show', item.id)" class="text-primary hover:text-primary/80 font-semibold">
                                        Proses
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

<style scoped>
@keyframes wave-animation {
  0%, 60%, 100% { transform: rotate(0deg); }
  10%, 30% { transform: rotate(14deg); }
  20%, 40% { transform: rotate(-8deg); }
  50% { transform: rotate(10deg); }
}
.animate-\[wave_2\.5s_ease-in-out_infinite\] {
  animation: wave-animation 2.5s ease-in-out infinite;
}
</style>