<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import OrnamentDivider from '@/components/OrnamentDivider.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import axios from 'axios';

const showEmailPassword = ref(false);
const checkingStudent = ref(false);
const studentMessage = ref('');
const studentError = ref('');
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    nim: '',
    nama: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const checkStudent = async () => {
    if (!form.nim || !form.nama) {
        studentError.value = 'Masukkan NIM dan Nama lengkap Anda.';
        return;
    }

    checkingStudent.value = true;
    studentError.value = '';
    studentMessage.value = '';

    try {
        const response = await axios.post(route('check.student'), {
            nim: form.nim,
            nama: form.nama,
        });

        if (response.data.exists) {
            showEmailPassword.value = true;
            studentMessage.value = response.data.message;
        } else {
            studentError.value = response.data.message;
            showEmailPassword.value = false;
        }
    } catch (error: any) {
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            studentError.value = Object.values(errors).flat().join(', ');
        } else {
            studentError.value = 'Terjadi kesalahan saat memeriksa data mahasiswa.';
        }
        showEmailPassword.value = false;
    } finally {
        checkingStudent.value = false;
    }
};

const resetForm = () => {
    showEmailPassword.value = false;
    studentMessage.value = '';
    studentError.value = '';
    form.reset();
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase>
        <Head title="Daftar Akun Baru" />

        <!-- Register Card with Musma Design Tokens -->
        <div class="relative bg-[#FFFDFB] rounded-3xl p-8 max-w-md w-full shadow-2xl border border-[#3B0613]/15 overflow-hidden">
            <!-- Balinese corner ornament watermark -->
            <img src="/image 63.png" class="absolute -top-3 -right-3 w-24 h-auto opacity-20 pointer-events-none select-none" alt="" />

            <!-- Card Header -->
            <div class="flex items-center gap-3.5 mb-3 relative z-10">
                <img src="/logo 2026.png" alt="Logo" class="h-10 w-auto object-contain select-none" />
                <div>
                    <p class="text-[10px] font-bold tracking-[0.2em] text-[#DA8278] uppercase">MUSMA LM FMIPA {{ new Date().getFullYear() }}</p>
                    <h2 class="font-serif font-bold text-2xl text-[#3B0613] leading-tight">Daftar Akun Baru</h2>
                </div>
            </div>

            <!-- Flourish Divider -->
            <OrnamentDivider variant="large" class="mb-6 relative z-10" />

            <form method="POST" @submit.prevent="submit" class="flex flex-col gap-4 relative z-10">
                <!-- Step 1: NIM and Nama -->
                <div class="flex flex-col gap-1.5">
                    <label for="nim" class="text-xs font-bold text-[#3B0613] tracking-wide">
                        NIM <span class="text-red-600">*</span>
                    </label>
                    <input
                        id="nim"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="nim"
                        v-model="form.nim"
                        placeholder="10 Digit NIM sesuai IMISSU"
                        :disabled="showEmailPassword"
                        class="w-full px-4 py-2.5 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25 disabled:opacity-60"
                        style="background-color:#FAF7F2; border-color:rgba(59,6,19,0.18); color:#3B0613;"
                    />
                    <InputError :message="form.errors.nim" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="nama" class="text-xs font-bold text-[#3B0613] tracking-wide">
                        Nama Lengkap <span class="text-red-600">*</span>
                    </label>
                    <input
                        id="nama"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="nama"
                        v-model="form.nama"
                        placeholder="Nama Lengkap sesuai IMISSU"
                        :disabled="showEmailPassword"
                        class="w-full px-4 py-2.5 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25 disabled:opacity-60"
                        style="background-color:#FAF7F2; border-color:rgba(59,6,19,0.18); color:#3B0613;"
                    />
                    <InputError :message="form.errors.nama" />
                </div>

                <!-- Check Student Verification Button -->
                <div v-if="!showEmailPassword" class="flex flex-col gap-2 pt-2">
                    <button
                        type="button"
                        @click="checkStudent"
                        :disabled="checkingStudent || !form.nim || !form.nama"
                        class="w-full py-3 px-4 rounded-xl font-bold text-sm bg-[#3B0613] hover:bg-[#51071B] disabled:opacity-50 text-white shadow-md transition-all flex items-center justify-center gap-2"
                    >
                        <LoaderCircle v-if="checkingStudent" class="h-4 w-4 animate-spin" />
                        <span>Verifikasi Data</span>
                    </button>

                    <div v-if="studentError" class="text-xs font-semibold text-red-600 bg-red-50 p-2.5 rounded-lg border border-red-200">
                        {{ studentError }}
                    </div>

                    <div v-if="studentMessage" class="text-xs font-semibold text-green-700 bg-green-50 p-2.5 rounded-lg border border-green-200">
                        {{ studentMessage }}
                    </div>
                </div>

                <!-- Step 2: Email and Password (shown after verification) -->
                <template v-if="showEmailPassword">
                    <div class="flex flex-col gap-1.5">
                        <label for="email" class="text-xs font-bold text-[#3B0613] tracking-wide">
                            Alamat Email <span class="text-red-600">*</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            required
                            :tabindex="3"
                            autocomplete="email"
                            v-model="form.email"
                            placeholder="nama@student.unud.ac.id"
                            class="w-full px-4 py-2.5 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25"
                            style="background-color:#FAF7F2; border-color:rgba(59,6,19,0.18); color:#3B0613;"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="password" class="text-xs font-bold text-[#3B0613] tracking-wide">
                            Kata Sandi <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                :tabindex="4"
                                autocomplete="new-password"
                                v-model="form.password"
                                placeholder="Min. 8 karakter"
                                class="w-full px-4 py-2.5 pr-10 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25"
                                style="background-color:#FAF7F2; border-color:rgba(59,6,19,0.18); color:#3B0613;"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9A6A72] hover:text-[#3B0613]"
                            >
                                <Eye v-if="!showPassword" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="password_confirmation" class="text-xs font-bold text-[#3B0613] tracking-wide">
                            Konfirmasi Kata Sandi <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                required
                                :tabindex="5"
                                autocomplete="new-password"
                                v-model="form.password_confirmation"
                                placeholder="Ulangi kata sandi"
                                class="w-full px-4 py-2.5 pr-10 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25"
                                style="background-color:#FAF7F2; border-color:rgba(59,6,19,0.18); color:#3B0613;"
                            />
                            <button
                                type="button"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9A6A72] hover:text-[#3B0613]"
                            >
                                <Eye v-if="!showPasswordConfirmation" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="resetForm"
                            class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm border-2 border-[#3B0613] text-[#3B0613] hover:bg-[#3B0613]/5 transition-colors"
                        >
                            Kembali
                        </button>
                        <button
                            type="submit"
                            :tabindex="6"
                            :disabled="form.processing"
                            class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm bg-[#3B0613] hover:bg-[#51071B] text-white shadow-md transition-all flex items-center justify-center gap-1.5"
                        >
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <span>Buat Akun</span>
                        </button>
                    </div>
                </template>

                <div class="text-center text-xs text-[#5A2A34] pt-2">
                    Sudah memiliki akun?
                    <Link href="/login" class="font-bold text-[#3B0613] underline underline-offset-4 hover:text-[#51071B]">
                        Masuk disini
                    </Link>
                </div>
            </form>
        </div>
    </AuthBase>
</template>
