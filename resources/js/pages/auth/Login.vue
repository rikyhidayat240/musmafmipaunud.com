<script setup lang="ts">
import InputError from "@/components/InputError.vue";
import AuthBase from "@/layouts/AuthLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { LoaderCircle, Eye, EyeOff } from "lucide-vue-next";
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
        <Head title="Masuk" />

        <!-- Login Card -->
        <div class="musma-card shadow-xl overflow-hidden">
            <!-- Card header band -->
            <div class="py-5 px-6 flex items-center gap-3" style="background-color:#51071B;">
                <img src="/logo 2026.png" alt="Logo" class="h-10 w-auto object-contain" />
                <div class="flex flex-col leading-tight">
                    <span class="text-xs text-white/60 font-medium uppercase tracking-widest">MUSMA LM FMIPA {{ new Date().getFullYear() }}</span>
                    <span class="text-base font-bold text-white">Login Pemilih</span>
                </div>
            </div>

            <!-- Decorative divider -->
            <div class="flex items-center justify-center py-4">
                <div class="musma-divider w-4/5">
                    <span class="text-xs text-[#C5AA6C] select-none">✦</span>
                </div>
            </div>

            <!-- Form body -->
            <div class="px-8 pb-8">
                <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-700 bg-green-50 rounded-lg px-4 py-2">
                    {{ status }}
                </div>

                <form method="POST" @submit.prevent="submit" class="flex flex-col gap-5">
                    <!-- NIM field -->
                    <div class="flex flex-col gap-1.5">
                        <label for="email" class="text-sm font-semibold text-[#51071B]">
                            Nomor Induk Mahasiswa (NIM)
                        </label>
                        <input
                            id="email"
                            type="text"
                            v-model="form.email"
                            placeholder="Contoh: 2008511001"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full px-4 py-2.5 rounded-lg text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#51071B]/30"
                            style="background-color:#FFFFFF; border-color:#E3D7D9; color:#51071B;"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Password field -->
                    <div class="flex flex-col gap-1.5">
                        <label for="password" class="text-sm font-semibold text-[#51071B]">Password</label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password"
                                class="w-full px-4 py-2.5 pr-11 rounded-lg text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#51071B]/30"
                                style="background-color:#FFFFFF; border-color:#E3D7D9; color:#51071B;"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#7A4A52] hover:text-[#51071B] transition-colors"
                            >
                                <Eye v-if="!showPassword" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="musma-btn w-full mt-2 py-3 text-base"
                    >
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Masuk ke Bilik Suara
                    </button>

                    <!-- Cancel link -->
                    <div class="text-center">
                        <Link href="/" class="text-sm font-medium text-[#7A4A52] hover:text-[#51071B] transition-colors">
                            Batal / Kembali
                        </Link>
                    </div>
                </form>
            </div>
        </div>

    </AuthBase>
</template>
