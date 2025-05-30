<script setup lang="ts">
import { ref, computed } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Eye, EyeOff } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const passwordFieldType = computed(() => {
    return showPassword.value ? 'text' : 'password';
});

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            if (Object.keys(form.errors).length > 0) {
                form.reset('password');
            }
        }
    });
};
</script>

<template>
    <Head title="Log in" />
    <div class="min-h-screen flex items-center justify-center bg-slate-500 p-4 sm:p-6 lg:p-8">
        <div class="flex w-full max-w-6xl shadow-2xl rounded-lg overflow-hidden">
            
            <div class="w-full md:w-1/2 hidden md:flex items-center justify-center p-6 lg:p-10 bg-slate-700">
                <img
                    src="/images/gedung-filkom.svg"
                    alt="Gedung FILKOM UB"
                    class="object-contain rounded-md max-h-[70vh] w-auto" 
                />
            </div>

            <div class="w-full md:w-1/2 flex flex-col items-center justify-center p-6 sm:p-10 lg:p-12 bg-slate-700">
                <div class="w-full max-w-md">
                    <div class="flex justify-center mb-6">
                        <img
                            src="/images/logo-si-transkrip-putih.svg"
                            alt="SI-Transkrip Logo"
                            class="h-10 sm:h-12"
                        />
                    </div>

                    <h1 class="text-2xl lg:text-3xl font-semibold text-center text-white mb-2">
                        Login to Your Account
                    </h1>
                    <p class="text-sm text-center text-slate-300 mb-8">
                        Masukkan email dan password untuk melanjutkan.
                    </p>

                    <div v-if="status" class="mb-4 text-sm font-medium text-green-300 bg-green-700/50 p-3 rounded-md text-center">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="grid gap-2">
                            <Label for="email" class="text-slate-200">Email</Label>
                            <Input
                                id="email"
                                type="text" 
                                required
                                autofocus
                                tabindex="1"
                                autocomplete="username" 
                                v-model="form.email"
                                placeholder="Ketik Email Anda.."
                                class="bg-white border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-orange-500"
                            />
                            <InputError :message="form.errors.email" class="text-red-400" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-slate-200">Password</Label>
                                <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-xs text-orange-400 hover:text-orange-300 underline" :tabindex="5">
                                    Forgot password?
                                </TextLink>
                            </div>
                            <div class="relative">
                                <Input
                                    id="password"
                                    :type="passwordFieldType"
                                    required
                                    tabindex="2"
                                    autocomplete="current-password"
                                    v-model="form.password"
                                    placeholder="Ketik password Anda.."
                                    class="bg-white border-slate-300 text-slate-800 focus:border-orange-500 focus:ring-orange-500 pr-10"
                                />
                                <button
                                    type="button"
                                    @click="togglePasswordVisibility"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-700 focus:outline-none"
                                    tabindex="-1"
                                    aria-label="Toggle password visibility"
                                >
                                    <EyeOff v-if="showPassword" class="h-5 w-5" />
                                    <Eye v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" class="text-red-400" />
                        </div>

                        <div class="flex items-center mt-1">
                            <Checkbox id="remember" name="remember" v-model:checked="form.remember" tabindex="3" class="border-slate-400 focus:ring-orange-500 text-orange-500" />
                            <Label for="remember" class="ml-2 text-sm text-slate-300">Remember me</Label>
                        </div>

                        <Button
                            type="submit"
                            class="mt-4 w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3"
                            tabindex="4"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Login
                        </Button>
                    </form>

                    <div class="mt-8 text-center text-sm">
                        <span class="text-slate-300">Don't have an account? </span>
                        <TextLink :href="route('register')" class="font-medium text-orange-400 hover:text-orange-300 underline underline-offset-4" :tabindex="6">
                            Sign up here
                        </TextLink>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>