<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// Impor Select tidak diperlukan karena kita menggunakan select HTML standar
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    nama_lengkap: '',
    nim: '',
    email: '',
    program_studi: '',
    password: '',
    password_confirmation: '',
});

const programStudiOptions = [
    { value: '', label: 'Pilih program studi Anda', disabled: true },
    { value: 'Sistem_Informasi', label: 'S1 Sistem Informasi' },
    { value: 'Teknologi_Informasi', label: 'S1 Teknologi Informasi' },
    { value: 'Pendidikan_Teknologi_Informasi', label: 'S1 Pendidikan Teknologi Informasi' },
    { value: 'Teknik_Informatika', label: 'S1 Teknik Informatika' },
    { value: 'Teknik_Komputer', label: 'S1 Teknik Komputer' },
];

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            if (Object.keys(form.errors).length > 0) {
                form.reset('password', 'password_confirmation');
            }
        },
    });
};
</script>

<template>
    <Head title="Register" />
    <div class="min-h-screen flex items-center justify-center bg-slate-500 p-4 sm:p-6 lg:p-8">
        <div class="flex w-full max-w-6xl shadow-2xl rounded-lg overflow-hidden"> 
            <div class="w-full md:w-1/2 hidden md:flex items-center justify-center p-6 sm:p-10 lg:p-12 bg-slate-700">
                <img
                    src="/images/gedung-filkom.svg" alt="Gedung FILKOM UB"
                    class="object-contain rounded-md max-h-[75vh] w-auto"
                />
            </div>

           <div class="w-full md:w-1/2 flex flex-col items-center justify-center p-6 sm:p-10 lg:p-12 bg-slate-700">
                <div class="w-full max-w-md">
                    <div class="flex justify-right mb-6">
                        <img
                            src="/images/logo-si-transkrip-putih.svg" alt="SI-Transkrip Logo"
                            class="h-10 sm:h-12"
                        />
                    </div>

                    <h1 class="text-2xl lg:text-3xl font-semibold text-center text-white mb-2">
                        Create Your Account
                    </h1>
                    <p class="text-sm text-center text-slate-300 mb-8">
                        Fill in the details below to join the platform.
                    </p>

                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="grid gap-2">
                            <Label for="nama_lengkap" class="text-slate-200">Nama Lengkap</Label>
                            <Input
                                id="nama_lengkap"
                                type="text"
                                required
                                autofocus
                                tabindex="1"
                                autocomplete="name"
                                v-model="form.nama_lengkap"
                                placeholder="Masukkan nama lengkap Anda"
                                class="bg-white border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-orange-500"
                            />
                            <InputError :message="form.errors.nama_lengkap" class="text-red-400" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="nim" class="text-slate-200">NIM (Nomor Induk Mahasiswa)</Label>
                            <Input
                                id="nim"
                                type="text"
                                required
                                tabindex="2"
                                autocomplete="username"
                                v-model="form.nim"
                                placeholder="Masukkan NIM Anda"
                                class="bg-white border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-orange-500"
                            />
                            <InputError :message="form.errors.nim" class="text-red-400" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" class="text-slate-200">Alamat Email</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabindex="3"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="contoh@student.ub.ac.id"
                                class="bg-white border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-orange-500"
                            />
                            <InputError :message="form.errors.email" class="text-red-400" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="program_studi" class="text-slate-200">Program Studi</Label>
                            <select
                                id="program_studi"
                                v-model="form.program_studi"
                                required
                                tabindex="4"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm text-slate-800"
                            >
                                <option v-for="option in programStudiOptions" :key="option.value" :value="option.value" :disabled="option.disabled">
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.program_studi" class="text-red-400" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password" class="text-slate-200">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                tabindex="5"
                                autocomplete="new-password"
                                v-model="form.password"
                                placeholder="Masukkan password Anda"
                                class="bg-white border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-orange-500"
                            />
                            <InputError :message="form.errors.password" class="text-red-400" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation" class="text-slate-200">Konfirmasi Password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                tabindex="6"
                                autocomplete="new-password"
                                v-model="form.password_confirmation"
                                placeholder="Ketik ulang password Anda"
                                class="bg-white border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-orange-500"
                            />
                            <InputError :message="form.errors.password_confirmation" class="text-red-400" />
                        </div>

                        <Button
                            type="submit"
                            class="mt-3 w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3"
                            tabindex="7"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Create Account
                        </Button>
                    </form>

                    <div class="mt-8 text-center text-sm">
                        <span class="text-slate-300">Already have an account? </span>
                        <TextLink :href="route('login')" class="font-medium text-orange-400 hover:text-orange-300 underline underline-offset-4" :tabindex="8">
                            Log in here
                        </TextLink>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
select:required:invalid {
  color: #6b7280; /* Warna placeholder abu-abu (Tailwind gray-500), sesuaikan jika perlu */
}
/* Style tambahan jika perlu */
</style>