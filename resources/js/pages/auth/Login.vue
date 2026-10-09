<script setup lang="ts">
import InputError from "@/components/InputError.vue";
import AuthBase from "@/layouts/AuthLayout.vue";
import OrnamentDivider from "@/components/OrnamentDivider.vue";
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
        <Head title="Masuk - Login Pemilih" />

        <!-- Login Card matching Figma Node 251:936 -->
        <div class="relative bg-[#FFFDFB] rounded-3xl p-8 max-w-md w-full shadow-2xl border border-[#3B0613]/15 overflow-hidden">
            <!-- Balinese corner ornament watermark -->
            <img src="/image 63.png" class="absolute -top-3 -right-3 w-24 h-auto opacity-20 pointer-events-none select-none" alt="" />

            <!-- Card Header -->
            <div class="flex items-center gap-3.5 mb-3 relative z-10">
                <img src="/logo 2026.png" alt="Logo" class="h-10 w-auto object-contain select-none" />
                <div>
                    <p class="text-[10px] font-bold tracking-[0.2em] text-[#DA8278] uppercase">MUSMA LM FMIPA {{ new Date().getFullYear() }}</p>
                    <h2 class="font-serif font-bold text-2xl text-[#3B0613] leading-tight">Login Pemilih</h2>
                </div>
            </div>

            <!-- Flourish Divider -->
            <OrnamentDivider variant="large" class="mb-6 relative z-10" />

            <!-- Status banner if any -->
            <div v-if="status" class="mb-4 text-center text-sm font-medium text-[#3B0613] bg-[#F5ECEE] rounded-xl px-4 py-2.5 border border-[#3B0613]/20">
                {{ status }}
            </div>

            <!-- Form -->
            <form method="POST" @submit.prevent="submit" class="flex flex-col gap-5 relative z-10">
                <!-- NIM field -->
                <div class="flex flex-col gap-1.5">
                    <label for="email" class="text-xs font-bold text-[#3B0613] tracking-wide">
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
                        class="w-full px-4 py-3 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25"
                        style="background-color:#FAF7F2; border-color:rgba(59,6,19,0.18); color:#3B0613;"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password field -->
                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-xs font-bold text-[#3B0613] tracking-wide">
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
                            class="w-full px-4 py-3 pr-11 rounded-xl text-sm border transition-all focus:outline-none focus:ring-2 focus:ring-[#3B0613]/25"
                            style="background-color:#FAF7F2; border-color:rgba(59,6,19,0.18); color:#3B0613;"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#9A6A72] hover:text-[#3B0613] transition-colors"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full mt-2 py-3.5 px-6 rounded-xl font-bold text-sm bg-[#3B0613] hover:bg-[#51071B] text-white shadow-lg transition-transform hover:scale-[1.01] flex items-center justify-center gap-2"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-1" />
                    <span>Masuk ke Bilik Suara</span>
                </button>

                <!-- Cancel link -->
                <div class="text-center mt-1">
                    <Link href="/" class="text-xs font-bold text-[#DA8278] hover:text-[#3B0613] transition-colors">
                        Batal / Kembali
                    </Link>
                </div>
            </form>
        </div>

    </AuthBase>
</template>
