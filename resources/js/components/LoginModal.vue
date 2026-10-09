<script setup lang="ts">
import InputError from "@/components/InputError.vue";
import OrnamentDivider from "@/components/OrnamentDivider.vue";
import { useForm } from "@inertiajs/vue3";
import { Eye, EyeOff, LoaderCircle } from "lucide-vue-next";
import { ref } from "vue";
import { route } from "ziggy-js";
import dayjs from "dayjs";

const props = withDefaults(
  defineProps<{
    open: boolean;
    year?: number | string;
  }>(),
  {
    open: false,
    year: () => dayjs().year(),
  }
);

const emit = defineEmits<{
  (e: "close"): void;
}>();

const showPassword = ref(false);

const form = useForm({
  email: "",
  password: "",
  remember: true,
});

const submit = () => {
  form.post(route("login"), {
    onSuccess: () => {
      emit("close");
    },
    onFinish: () => {
      form.reset("password");
    },
  });
};
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#3B0613]/60 backdrop-blur-sm transition-opacity"
    @click.self="emit('close')"
  >
    <div
      class="relative w-full max-w-[460px] bg-[#FFFDFB] rounded-3xl p-7 sm:p-9 shadow-2xl border border-[#3B0613]/20 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200"
    >
      <!-- Top Subtle Background Flueron watermark -->
      <img
        src="/image 63.png"
        class="absolute -top-6 -right-6 w-32 h-auto opacity-10 pointer-events-none select-none rotate-180"
        alt=""
      />

      <!-- Header with Logo -->
      <div class="flex items-center gap-3.5 mb-2">
        <img src="/logo 2026.png" alt="Logo" class="h-11 w-auto object-contain shrink-0" />
        <div class="flex flex-col">
          <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#DA8278]">
            MUSMA LM FMIPA {{ year }}
          </span>
          <h2 class="font-serif font-bold text-2xl text-[#3B0613] leading-tight">
            Login Pemilih
          </h2>
        </div>
      </div>

      <!-- Ornamental Divider -->
      <OrnamentDivider size="sm" class="my-3" />

      <!-- Login Form -->
      <form @submit.prevent="submit" class="flex flex-col gap-4 mt-1">
        <!-- NIM / Identifier Field -->
        <div class="flex flex-col gap-1.5 text-left">
          <label for="modal-nim" class="text-xs sm:text-sm font-semibold text-[#3B0613]">
            Nomor Induk Mahasiswa (NIM)
          </label>
          <input
            id="modal-nim"
            type="text"
            v-model="form.email"
            placeholder="Contoh: 24059328"
            required
            autofocus
            autocomplete="username"
            class="w-full px-4 py-3 rounded-xl text-sm border bg-white text-[#3B0613] placeholder:text-[#9A6A72]/60 border-[#3B0613]/25 focus:border-[#3B0613] focus:ring-2 focus:ring-[#3B0613]/15 transition-all outline-none"
          />
          <InputError :message="form.errors.email" />
        </div>

        <!-- Password Field -->
        <div class="flex flex-col gap-1.5 text-left">
          <label for="modal-password" class="text-xs sm:text-sm font-semibold text-[#3B0613]">
            Password
          </label>
          <div class="relative">
            <input
              id="modal-password"
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              placeholder="Masukkan password"
              required
              autocomplete="current-password"
              class="w-full px-4 py-3 pr-11 rounded-xl text-sm border bg-white text-[#3B0613] placeholder:text-[#9A6A72]/60 border-[#3B0613]/25 focus:border-[#3B0613] focus:ring-2 focus:ring-[#3B0613]/15 transition-all outline-none"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9A6A72] hover:text-[#3B0613] p-1 transition-colors"
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
          class="w-full mt-2 py-3.5 px-6 rounded-xl font-bold text-sm text-white bg-[#3B0613] hover:bg-[#50071B] active:scale-[0.99] transition-all shadow-md hover:shadow-lg disabled:opacity-60 flex items-center justify-center gap-2"
        >
          <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
          <span>Masuk ke Bilik Suara</span>
        </button>

        <!-- Cancel Link -->
        <button
          type="button"
          @click="emit('close')"
          class="text-xs sm:text-sm font-semibold text-[#DA8278] hover:text-[#3B0613] transition-colors py-1 text-center"
        >
          Batal / Kembali
        </button>
      </form>
    </div>
  </div>
</template>
