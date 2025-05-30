<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'; // Asumsi path ke komponen Button Anda
import AppLogo from '@/components/AppLogo.vue';   // Asumsi path ke komponen AppLogo Anda
</script>

<template>
    <Head title="Selamat Datang di SI-Transkrip" />
    <div class="landing-page min-h-screen relative overflow-hidden flex items-center justify-center p-4">
        <div class="decor-bottom-image">
            <img src="/images/dekorasi-bawah.png" alt="Dekorasi Bawah" class="opacity-70 md:opacity-90" />
        </div>
        <div class="decor-left-image">
            <img src="/images/dekorasi-kiri.png" alt="Dekorasi Kiri" class="opacity-70 md:opacity-90" />
        </div>
        <div class="decor-right-image">
            <img src="/images/dekorasi-kanan.png" alt="Dekorasi Kanan" class="opacity-70 md:opacity-90" />
        </div>
        <div class="decor-top-image">
            <img src="/images/dekorasi-kanan.png" alt="Dekorasi Atas Kanan" class="opacity-70 md:opacity-90" />
        </div>

        <div class="decor-circle top-left"></div>
        <div class="decor-circle bottom-left"></div>
        <div class="decor-circle bottom-left2"></div>
        <div class="decor-circle top-right"></div>
        <div class="decor-circle top-right2"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between w-full max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="left-section md:w-1/2 lg:w-3/5 text-center md:text-left py-8 md:py-0">
                <div class="inline-block md:block mb-0 md:mb-4">
                <img src="/images/logo-si-transkrip-putih.svg" alt="Logo SI-Transkrip" class="logo" />
                </div>

                <div class="teks-bawah mt-8 md:mt-12">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4">
                        <span class="text-highlight-landing">SI-</span>Transkrip
                    </h1>
                    <p class="text-lg sm:text-xl mb-6 opacity-90">
                        <span class="text-highlight-landing">Sistem Informasi</span> Pengajuan Transkrip Akademik FILKOM UB
                    </p>
                    <p class="text-sm sm:text-base mb-10 leading-relaxed max-w-lg mx-auto md:mx-0 opacity-80">
                        Kelola pengajuan transkrip akademik tanpa repot. Proses lebih cepat, transparan, dan terintegrasi langsung dengan SIAM.
                    </p>

                    <Link :href="route('login')">
                        <button class="tombol-login-custom">
                            Login Sekarang
                        </button>
                    </Link>
                </div>
            </div>
        </div>

        <div class="right-section-gedung">
            <img src="/images/gedung-filkom.svg" alt="Gedung FILKOM" class="gedung-img" />
        </div>
    </div>
</template>

<style scoped>
/* Styling utama untuk landing page dikembalikan sesuai permintaan Anda */
.landing-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #3f518b, #676eaf); /* Gradient biru */
  color: white; /* Warna teks default putih */
  font-family: 'Roboto', sans-serif; /* Font Roboto */
  position: relative; /* Sudah ada dari kelas Tailwind 'relative' */
  overflow: hidden; /* Sudah ada dari kelas Tailwind 'overflow-hidden' */
  /* display: flex; items-center; justify-center; p-4; sudah ada dari kelas Tailwind */
}

/* Warna highlight spesifik untuk landing page */
.text-highlight-landing {
  color: #E2B39C; /* Warna highlight peachy-orange Anda */
}

/* Tombol login kustom jika Button dari UI Kit tidak cocok */
.tombol-login-custom {
  background-color: #CB774D; /* Warna oranye-coklat Anda */
  color: white;
  padding: 0.75rem 2rem; /* 12px 32px */
  border-radius: 0.5rem; /* 8px */
  text-decoration: none;
  font-weight: 500; /* Medium */
  transition: background-color 0.3s ease;
  display: inline-block; /* Agar padding dan margin bekerja dengan baik */
  box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.1); /* Shadow halus */
}

.tombol-login-custom:hover {
  background-color: #e88b65; /* Warna hover Anda */
  transform: translateY(-1px); /* Efek hover naik sedikit */
}


.right-section-gedung {
  position: absolute;
  bottom: 0;
  right: 0;
  z-index: 5;
  pointer-events: none;
}

.gedung-img {
  max-height: 125vh;
  height: auto;
  width: auto;
  max-width: 600px;
  object-fit: contain;
  filter: drop-shadow(0 0 25px rgba(226, 179, 156, 0.4)); /* Shadow dengan warna highlight */
}

.decor-circle {
  position: absolute;
  width: 30px;
  height: 30px;
  background-color: #f2a37b; /* Warna lingkaran dekorasi Anda */
  border-radius: 50%;
  z-index: 1;
  box-shadow: inset -3px -3px 8px rgba(255, 255, 255, 0.25), 2px 2px 5px rgba(0,0,0,0.2);
}

.top-left { top: 155px; left: 18px; }
.bottom-left { bottom: 90px; left: 55%; transform: translateX(-50%); }
.bottom-left2 { bottom: 65px; left: 65%; transform: translateX(-50%); }
.top-right { width: 30px; height: 30px; top: 205px; right: 25%; }
.top-right2 { width: 30px; height: 30px; top: 290px; right: 30%; }

.decor-left-image { position: absolute; top: 5px; left: 0px; z-index: 1; }
.decor-left-image img { width: 98px; }

.decor-bottom-image { position: absolute; bottom: 0; left: 50%; transform: translate(-30%, 0px); z-index: 1; }
.decor-bottom-image img { width: 400px; max-width: 80vw; }

.decor-right-image { position: absolute; top: 300px; right: 45px; z-index: 1; }
.decor-right-image img { width: 200px; }

.decor-top-image { position: absolute; top: 190px; right: 20%; z-index: 1; }
.decor-top-image img { width: 200px; }

/* Opacity untuk gambar dekorasi bisa ditambahkan di sini atau via kelas Tailwind */
.decor-left-image img, .decor-bottom-image img, .decor-right-image img, .decor-top-image img {
    opacity: 0.8; /* Contoh opacity default */
}

@media (max-width: 1024px) { /* lg */
  .gedung-img { max-height: 60vh; max-width: 350px; }
  .decor-bottom-image { transform: translate(-40%, 0px); }
  .bottom-left { left: 45%; }
  .bottom-left2 { left: 55%; }
  .top-right { right: 15%; }
  .top-right2 { right: 20%; }
  .decor-top-image { right: 10%; }
}

@media (max-width: 768px) { /* md */
  .right-section-gedung { opacity: 0.5; }
  .gedung-img { max-height: 50vh; max-width: 280px; }
  .decor-bottom-image, .decor-right-image, .decor-top-image, .decor-left-image, .decor-circle {
    transform: scale(0.8);
  }
  .decor-bottom-image { transform: translate(-50%, 10%) scale(0.8); left: 50%; }
  .bottom-left { left: 10%; bottom: 5%;}
  .bottom-left2 { display: none; }
  .top-right { top: 5%; right: 5%; }
  .top-right2 { display: none; }
  .decor-top-image { top: 10%; right: 5%;}
  .decor-left-image { top: 1%; left: 1%;}
}
</style>