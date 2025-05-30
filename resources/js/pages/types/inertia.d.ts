import { PageProps as InertiaBasePageProps } from '@inertiajs/core';

// Definisikan tipe untuk pengguna yang terautentikasi
// Sesuaikan properti ini dengan apa yang dikirim oleh backend Laravel Anda
// untuk Auth::user() (misalnya dari UserResource atau saat user di-share secara global)
export interface AuthenticatedUser {
    id: number;
    nama: string; // Atau nama_lengkap jika itu yang Anda gunakan di model User dan kirim ke frontend
    nim?: string;
    email: string;
    program_studi?: string; // Ini adalah ENUM string dari backend
    // tambahkan properti lain yang mungkin ada, contoh:
    // nama_lengkap?: string;
    // foto_profil_url?: string;
    // role?: string;
}

// Definisikan tipe untuk prop 'auth'
export interface AuthProps {
    user: AuthenticatedUser | null; // User bisa null jika tidak ada yang login
}

// Sekarang, perluas (augment) interface PageProps global dari Inertia
declare module '@inertiajs/core' {
  interface PageProps extends InertiaBasePageProps {
    auth: AuthProps; // Tambahkan properti 'auth' kita
    flash?: { // Contoh jika Anda juga men-share flash messages secara global
        success?: string;
        error?: string;
        // tambahkan tipe flash message lain jika ada
    };
    // Anda juga bisa menambahkan props lain yang di-share secara global di sini
  }
}

// Anda juga bisa mendefinisikan tipe untuk props halaman spesifik di sini jika mau,
// tapi untuk error ini, augmentasi PageProps di atas yang utama.
// Contoh untuk props spesifik halaman Create.vue:
export interface KeperluanOption {
    id: string | number;
    nama_keperluan: string;
    disabled?: boolean;
}

export interface CreatePengajuanPageProps extends InertiaBasePageProps {
    auth: AuthProps;
    keperluanPengajuanOptions?: KeperluanOption[];
    flash?: {
        success?: string;
        error?: string;
    };
}

// Pastikan file ini dikenali oleh TypeScript compiler Anda
// Biasanya, tsconfig.json Anda akan otomatis menyertakan semua file .d.ts
// di dalam direktori proyek atau dalam path yang ditentukan di 'include'.