<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
// NavUser dan NavFooter tidak akan kita gunakan di footer sidebar sesuai desain baru
// import NavUser from '@/components/NavUser.vue';
// import NavFooter from '@/components/NavFooter.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button'; // Import Button untuk Logout
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3'; // Import usePage
import { computed } from 'vue'; // Import computed

// Import Ikon dari lucide-vue-next
import { 
    LayoutGrid, 
    ClipboardList, 
    FileMinus2, 
    LogOut,
    History,
    ShieldCheck, // Untuk Admin Dashboard
    Users,      // Untuk Manajemen Pengguna (Admin)
    Database,   // Untuk Data Master (Admin)
    // Jika ada ikon lain yang dibutuhkan, tambahkan di sini
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue'; // Pastikan path ini benar

const page = usePage();
// Asumsi 'role' ada di page.props.auth.user
// Sesuaikan tipe AuthUser jika Anda punya definisi global yang lebih spesifik
const userRole = computed(() => (page.props.auth?.user as { role?: string })?.role);

// --- Item Navigasi untuk Mahasiswa ---
const mahasiswaNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Daftar Pengajuan Transkrip',
        href: route('pengajuan.index'), // Nama route untuk daftar pengajuan transkrip mahasiswa
        icon: ClipboardList,
    },
    {
        title: 'Daftar Penghapusan Mata Kuliah',
        href: route('pengajuan.hapus-mk.index'), // Nama route untuk daftar pengajuan hapus MK mahasiswa
        icon: FileMinus2,
    },
    // Tambahkan link ke form create jika diinginkan di sidebar juga
    // { title: 'Buat Pengajuan Transkrip', href: route('pengajuan.create'), icon: PlusCircle },
    // { title: 'Buat Pengajuan Hapus MK', href: route('pengajuan.hapus-mk.create'), icon: PlusCircle },
];

// --- Item Navigasi untuk Admin (Operator Akademik) ---
const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'), // Pastikan route 'admin.dashboard' ada
        icon: ShieldCheck,
    },
    {
        title: 'Pengajuan Transkrip',
        href: route('admin.pengajuan.transkrip.index'), // Route yang sudah kita buat
        icon: ClipboardList,
    },
    {
        title: 'Pengajuan Hapus MK',
        href: route('admin.pengajuan.hapus-mk.index'), // Ganti dengan route('admin.pengajuan.hapus-mk.index') jika sudah ada
        icon: FileMinus2,
    },
    // {
    //     title: 'Manajemen Pengguna',
    //     href: '#', // Ganti dengan route('admin.users.index') jika sudah ada
    //     icon: Users,
    // },
    // {
    //     title: 'Data Master',
    //     href: '#', // Ganti dengan route ke halaman utama data master jika ada
    //     icon: Database,
    // },
];

const kpsNavItems: NavItem[] = [
    {
        title: 'KPS Dashboard',
        href: route('kps.dashboard'), // Pastikan route 'kps.dashboard' ada
        icon: ShieldCheck, // Atau ikon lain yang sesuai untuk KPS
    },
    {
        title: 'Riwayat Persetujuan', // Atau hanya "Riwayat"
        href: route('kps.riwayat.index'), // Anda perlu membuat route ini nanti
        icon: History,
    },
];

// Computed property untuk memilih item navigasi berdasarkan peran
const navItemsToDisplay = computed(() => {
    if (userRole.value === 'admin') { // Ganti 'admin' dengan nama peran admin Anda yang sebenarnya
        return adminNavItems;
    } 
    else if (userRole.value === 'kps') { // Ganti 'kps' dengan nama peran KPS Anda
        return kpsNavItems; // Aktifkan jika sudah ada
    }
    else if (userRole.value === 'mahasiswa') { // Ganti 'mahasiswa' dengan nama peran mahasiswa Anda
        return mahasiswaNavItems;
    }
    // Default jika tidak ada peran yang cocok atau pengguna tidak login (meskipun ini seharusnya dilindungi middleware)
    return mahasiswaNavItems; // Atau return [] jika tidak ingin menampilkan apa pun
});

</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="flex flex-col h-screen"> 
        <SidebarHeader class="p-4"> 
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="justify-start"> 
                            <Link :href="userRole === 'admin' ? route('admin.dashboard') : (userRole === 'kps' ? route('kps.dashboard') : route('dashboard'))">
                            <AppLogo class="h-8 w-auto" /> 
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="flex-grow overflow-y-auto"> 
            <NavMain :items="navItemsToDisplay" />
        </SidebarContent>

        <SidebarFooter class="p-4 border-t border-border mt-auto"> 
            <Link :href="route('logout')" method="post" as="button" class="w-full">
                <Button variant="outline" class="w-full border-border text-foreground hover:bg-accent hover:text-accent-foreground">
                    <LogOut class="mr-2 h-4 w-4" />
                    Logout
                </Button>
            </Link>
        </SidebarFooter>
    </Sidebar>
    
 <slot />
   <slot /> 
</template>