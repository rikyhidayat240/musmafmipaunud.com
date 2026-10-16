<script setup lang="ts">
import InputError from "@/components/InputError.vue";
import AuthBase from "@/layouts/AuthLayout.vue";
import OrnamentDivider from "@/components/OrnamentDivider.vue";
import MaterialIcon from "@/components/MaterialIcon.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { route } from "ziggy-js";

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const showPassword = ref(false);

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <AuthBase>
        <Head title="Masuk - Login Pemilih" />

        <!-- Login Card matching Figma Node 251:936 -->
        <div class="relative bg-[#FFFDFB] dark:bg-[#27060F] rounded-3xl p-8 max-w-md w-full shadow-2xl border border-[#3B0613]/15 dark:border-[#FCCE88]/25 overflow-hidden">
            <!-- Balinese corner ornament watermark -->
            <img src="/image 63.png" class="absolute -top-3 -right-3 w-24 h-auto opacity-20 dark:opacity-10 pointer-events-none select-none" alt="" />

            <!-- Card Header -->
            <div class="flex items-center gap-3.5 mb-3 relative z-10">
                <img src="/logo 2026.png" alt="Logo" class="h-10 w-auto object-contain select-none" />
                <div>
                    <p class="text-[10px] font-bold tracking-[0.2em] text-[#DA8278] dark:text-[#FCCE88]/90 uppercase">MUSMA LM FMIPA {{ new Date().getFullYear() }}</p>
                    <h2 class="font-serif font-bold text-2xl text-[#3B0613] dark:text-[#FAF4EB] leading-tight">Login Pemilih</h2>
                </div>
            </div>

            <!-- Flourish Divider -->
            <OrnamentDivider variant="large" class="mb-6 relative z-10" />

            <!-- Status banner if any -->
            <div v-if="status" class="mb-4 text-center text-sm font-medium text-[#3B0613] dark:text-[#FAF4EB] bg-[#F5ECEE] dark:bg-[#3D0A17] rounded-xl px-4 py-2.5 border border-[#3B0613]/20 dark:border-[#FCCE88]/20">
                {{ status }}
            </div>

            <!-- Form -->
            <form method="POST" @submit.prevent="submit" class="flex flex-col gap-5 relative z-10">
                <!-- NIM field -->
                <div class="flex flex-col gap-1.5">
                    <label for="email" class="text-xs font-bold text-[#3B0613] dark:text-[#FAF4EB] tracking-wide">
                        Nomor Induk Mahasiswa (NIM)
                    </label>
                    <input
                        id="email"
                        type="text"
                        v-model="form.email"
                        placeholder="Contoh: 24059328"
                        required
                        autofocus
                        autocomplete="email"
                        class="w-full px-4 py-3 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25 dark:focus:ring-[#FCCE88]/20 bg-[#FAF7F2] dark:bg-[#190308] border-[#3B0613]/20 dark:border-[#FCCE88]/30 text-[#3B0613] dark:text-[#FAF4EB] placeholder:text-[#9A6A72]/60 dark:placeholder:text-[#FCCE88]/40"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password field -->
                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-xs font-bold text-[#3B0613] dark:text-[#FAF4EB] tracking-wide">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-3 pr-11 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25 dark:focus:ring-[#FCCE88]/20 bg-[#FAF7F2] dark:bg-[#190308] border-[#3B0613]/20 dark:border-[#FCCE88]/30 text-[#3B0613] dark:text-[#FAF4EB] placeholder:text-[#9A6A72]/60 dark:placeholder:text-[#FCCE88]/40"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#9A6A72] dark:text-[#FCCE88]/70 hover:text-[#3B0613] dark:hover:text-[#FCCE88] transition-colors flex items-center justify-center"
                        >
                            <MaterialIcon :icon="showPassword ? 'visibility_off' : 'visibility'" size="sm" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full mt-2 py-3.5 px-6 rounded-xl font-bold text-sm bg-[#3B0613] dark:bg-[#FCCE88] hover:bg-[#51071B] dark:hover:bg-[#FBE8B5] text-white dark:text-[#27060F] shadow-lg transition-transform hover:scale-[1.01] flex items-center justify-center gap-2"
                >
                    <MaterialIcon v-if="form.processing" icon="progress_activity" size="sm" class="animate-spin" />
                    <span>Masuk ke Bilik Suara</span>
                </button>

                <!-- Cancel link -->
                <div class="text-center mt-1">
                    <Link href="/" class="text-xs font-bold text-[#DA8278] dark:text-[#FCCE88]/80 hover:text-[#3B0613] dark:hover:text-[#FCCE88] transition-colors">
                        Batal / Kembali
                    </Link>
                </div>
            </form>
        </div>

    </AuthBase>
</template>
