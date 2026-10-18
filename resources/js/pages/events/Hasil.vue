<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import OrnamentDivider from '@/components/OrnamentDivider.vue';
import MaterialIcon from '@/components/MaterialIcon.vue';
import { Kegiatan } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import dayjs from 'dayjs';

const props = defineProps<{
  kegiatan?: Kegiatan | null
}>()

const isRefreshing = ref(false);

const refreshHasil = () => {
  isRefreshing.value = true;
  router.reload({
    only: ['kegiatan'],
    onFinish: () => {
      setTimeout(() => {
        isRefreshing.value = false;
      }, 400);
    },
  });
};

const candidatePalettes = [
  {
    gradient: 'linear-gradient(90deg, #3B0613, #7D2D3D)',
    badgeBg: '#3B0613',
    badgeText: '#FFFFFF',
    percentColor: '#DA8278',
  },
  {
    gradient: 'linear-gradient(90deg, #C5AA6C, #F5C249)',
    badgeBg: '#F5C249',
    badgeText: '#3B0613',
    percentColor: '#D49B27',
  },
  {
    gradient: 'linear-gradient(90deg, #134E4A, #2E8B78)',
    badgeBg: '#134E4A',
    badgeText: '#FFFFFF',
    percentColor: '#2E8B78',
  },
  {
    gradient: 'linear-gradient(90deg, #1E3A5F, #3B6EA5)',
    badgeBg: '#1E3A5F',
    badgeText: '#FFFFFF',
    percentColor: '#4A88C7',
  },
  {
    gradient: 'linear-gradient(90deg, #4A1D5E, #8446A0)',
    badgeBg: '#4A1D5E',
    badgeText: '#FFFFFF',
    percentColor: '#9B59B6',
  },
  {
    gradient: 'linear-gradient(90deg, #6B2D14, #C2592B)',
    badgeBg: '#6B2D14',
    badgeText: '#FFFFFF',
    percentColor: '#D96B38',
  },
];

const getPalette = (index: number) => candidatePalettes[index % candidatePalettes.length];

const totalSuara = computed(() =>
  props.kegiatan?.kandidat?.reduce((acc, val) => acc + (val.jumlah_suara || 0), 0) || 0
);
const kandidat = computed(() => {
  return props.kegiatan?.kandidat?.map(calon => ({
    ...calon,
    jumlah_suara_persen: totalSuara.value > 0 ? parseFloat(((calon.jumlah_suara || 0) / totalSuara.value * 100).toFixed(2)) : 0
  })) || []
})
</script>

<template>
  <Head title="Hasil Pemilihan" />
  <AppLayout>
    <div class="musma-bg min-h-full flex flex-col relative">

      <!-- Global Background Ornaments -->
      <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <img src="/logo 2026.png" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] md:w-[600px] h-auto object-contain opacity-[0.035] select-none" alt="" />
        <img src="/image 62.png" class="absolute top-0 left-0 w-[140px] md:w-[220px] h-auto object-contain opacity-25 rotate-180 -translate-x-1/4 -translate-y-1/4 select-none" alt="" />
        <img src="/image 63.png" class="absolute top-0 right-0 w-[110px] md:w-[180px] h-auto object-contain opacity-25 rotate-180 translate-x-1/4 -translate-y-1/4 select-none" alt="" />
        <img src="/image 63.png" class="absolute bottom-0 left-0 w-[110px] md:w-[180px] h-auto object-contain opacity-25 -translate-x-1/4 translate-y-1/4 select-none" alt="" />
        <img src="/image 62.png" class="absolute bottom-0 right-0 w-[140px] md:w-[220px] h-auto object-contain opacity-25 translate-x-1/4 translate-y-1/4 select-none" alt="" />
      </div>

      <!-- ===== HERO HEADER ===== -->
      <div class="relative w-full overflow-hidden border-b border-[#3B0613]/20" style="background: linear-gradient(135deg, #3B0613 0%, #20020A 100%); min-height: 180px;">
        <!-- Ornaments inside hero -->
        <img src="/image 62.png" class="absolute top-0 left-0 w-32 md:w-52 h-auto object-contain opacity-20 rotate-180 select-none pointer-events-none" alt="" />
        <img src="/image 63.png" class="absolute top-0 right-0 w-24 md:w-40 h-auto object-contain opacity-20 rotate-180 select-none pointer-events-none" alt="" />
        <img src="/image 61.png" class="absolute bottom-0 left-1/2 -translate-x-1/2 h-28 w-auto opacity-10 select-none pointer-events-none" alt="" />

        <!-- Header Content -->
        <div class="relative z-10 flex flex-col items-center justify-center py-12 px-4 text-center">
          <img src="/logo 2026.png" alt="Logo" class="h-14 w-auto object-contain mb-3 select-none" />
          <p class="text-xs font-bold tracking-[0.25em] uppercase text-[#F5C249] mb-1">
            HASIL PERHITUNGAN SUARA
          </p>
          <h1 class="font-serif text-2xl md:text-4xl font-bold text-white tracking-wide uppercase">
            {{ kegiatan && kegiatan.kandidat && kegiatan.kandidat.length > 0 ? kegiatan.nama : 'Hasil Pemilihan' }}
          </h1>
          <p class="text-xs text-white/70 tracking-widest mt-2 uppercase font-medium">
            MUSMA LM FMIPA &bull; {{ dayjs().year() }}
          </p>
        </div>
      </div>

      <!-- ===== MAIN CONTENT ===== -->
      <div class="relative z-10 max-w-6xl mx-auto px-4 pt-10 pb-16 w-full">

        <!-- No event state -->
        <div v-if="!kegiatan || !kegiatan.kandidat || kegiatan.kandidat.length === 0"
          class="flex flex-col items-center justify-center py-20 px-6 text-center bg-[#FFFDFB] dark:bg-[#27060F] rounded-3xl border border-[#3B0613]/15 dark:border-[#FCCE88]/20 shadow-md">
          <img src="/image 61.png" class="w-24 h-auto opacity-30 mb-4" alt="" />
          <h2 class="font-serif text-2xl font-bold text-[#3B0613] dark:text-[#FAF4EB] mb-2">Tidak Ada Pemilihan yang Berlangsung</h2>
          <p class="text-sm text-[#9A6A72] dark:text-[#D4BAC1] max-w-sm mb-6">Hasil perolehan suara akan ditampilkan secara otomatis setelah kegiatan pemilihan selesai.</p>
          <div class="flex flex-wrap items-center justify-center gap-3">
            <button
              type="button"
              @click="refreshHasil"
              :disabled="isRefreshing"
              class="px-5 py-2.5 rounded-xl font-bold text-sm bg-[#FFFDFB] dark:bg-[#1A0308] text-[#3B0613] dark:text-[#FCCE88] border border-[#3B0613]/20 dark:border-[#FCCE88]/30 hover:bg-[#FAF7F2] dark:hover:bg-[#2A050E] transition-all shadow-md inline-flex items-center gap-2 cursor-pointer disabled:opacity-60"
            >
              <MaterialIcon icon="refresh" :class="['text-lg', isRefreshing ? 'animate-spin' : '']" />
              <span>Perbarui</span>
            </button>
            <Link href="/" class="px-6 py-2.5 rounded-xl font-bold text-sm bg-[#3B0613] dark:bg-[#FCCE88] text-white dark:text-[#2A050E] hover:bg-[#51071B] dark:hover:bg-[#E2B771] transition-all shadow-md inline-flex items-center gap-2">
              <MaterialIcon icon="arrow_back" class="text-lg" />
              <span>Kembali ke Beranda</span>
            </Link>
          </div>
        </div>

        <div v-if="kegiatan && kegiatan.kandidat && kegiatan.kandidat.length > 0">

          <!-- Total suara summary badge -->
          <div class="flex justify-center mb-10">
            <div class="text-center px-12 py-6 shadow-xl rounded-3xl border border-[#3B0613]/20 dark:border-[#FCCE88]/25 relative overflow-hidden"
                 style="background: linear-gradient(135deg, #3B0613 0%, #51071B 100%);">
              <img src="/image 63.png" class="absolute -top-3 -right-3 w-16 h-auto opacity-15 rotate-180 pointer-events-none select-none" alt="" />
              <p class="text-xs font-bold tracking-[0.2em] uppercase mb-1 text-[#F5C249]">Total Suara Masuk</p>
              <p class="text-5xl md:text-6xl font-serif font-black text-white">{{ totalSuara }}</p>
              <p class="text-xs mt-1 text-white/60">Suara sah dari seluruh pemilih</p>
            </div>
          </div>

          <!-- Candidates Result Cards -->
          <div class="grid grid-cols-1 gap-6 lg:gap-8 items-stretch mx-auto"
               :class="kandidat.length >= 3 ? 'md:grid-cols-3 max-w-6xl' : 'md:grid-cols-2 max-w-4xl'">
            <div
              v-for="(calon, i) in kandidat"
              :key="calon.id"
              class="bg-[#FFFDFB] dark:bg-[#27060F] rounded-3xl overflow-hidden shadow-xl border border-[#3B0613]/15 dark:border-[#FCCE88]/20 flex flex-col relative"
            >
              <!-- Top accent banner -->
              <div class="h-2.5 w-full" :style="{
                background: getPalette(i).gradient
              }"></div>

              <!-- Number badge -->
              <div class="absolute top-5 left-5 w-12 h-12 rounded-full flex items-center justify-center shadow-lg z-10 font-serif font-bold text-xl border-2 border-white dark:border-[#FCCE88]/60"
                   :style="{
                     background: getPalette(i).badgeBg,
                     color: getPalette(i).badgeText,
                   }">
                {{ String(calon.no_urut).padStart(2, '0') }}
              </div>

              <!-- Photo section -->
              <div class="flex justify-center items-end pt-8 pb-0 relative overflow-hidden bg-gradient-to-b from-[#FAF7F2] to-[#F5ECEE] dark:from-[#350813] dark:to-[#1A0308]"
                   style="min-height: 250px;">
                <img
                  :src="calon.foto ? `/storage/${calon.foto}` : '/assets/blank-profile-picture.webp'"
                  class="relative z-10 w-auto h-56 object-cover object-top"
                  alt="Foto Kandidat"
                />
              </div>

              <!-- Info section -->
              <div class="p-6 flex-1 flex flex-col text-left">
                <!-- Name -->
                <h3 class="font-serif font-bold text-xl text-[#3B0613] dark:text-[#FAF4EB] mb-1">
                  <span v-for="(mhs, idx) in calon.mahasiswa" :key="mhs.nim">
                    {{ mhs.nama }}<span v-if="idx < (calon.mahasiswa?.length ?? 0) - 1"> &amp; </span>
                  </span>
                </h3>
                <p class="text-xs font-semibold text-[#9A6A72] dark:text-[#D4BAC1] mb-4">
                  {{ calon.mahasiswa?.[0]?.program_studi?.nama || 'FMIPA' }}
                  {{ calon.mahasiswa?.[0]?.angkatan ? String(calon.mahasiswa?.[0]?.angkatan).slice(-2) : '' }}
                </p>

                <!-- Divider -->
                <div class="my-2">
                  <OrnamentDivider variant="small" />
                </div>

                <!-- Vote count & percentage -->
                <div class="py-4 text-center">
                  <p class="text-5xl font-serif font-black text-[#3B0613] dark:text-[#FAF4EB] leading-none mb-1">{{ calon.jumlah_suara }}</p>
                  <p class="text-xs font-semibold uppercase tracking-wider text-[#9A6A72] dark:text-[#D4BAC1]">Suara Sah</p>
                </div>

                <!-- Percentage Bar -->
                <div class="mt-auto pt-2">
                  <div class="flex items-center justify-between mb-2 text-xs font-bold">
                    <span class="text-[#9A6A72] dark:text-[#D4BAC1]">Perolehan</span>
                    <span class="text-base font-serif" :style="{ color: getPalette(i).percentColor }">
                      {{ calon.jumlah_suara_persen }}%
                    </span>
                  </div>
                  <!-- Progress track -->
                  <div class="w-full h-4 rounded-full overflow-hidden bg-[#FAF7F2] dark:bg-[#1A0308] border border-[#3B0613]/15 dark:border-[#FCCE88]/20">
                    <div
                      class="h-full rounded-full transition-all duration-1000 ease-out"
                      :style="{
                        width: `${calon.jumlah_suara_persen}%`,
                        background: getPalette(i).gradient
                      }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Action Buttons: Refresh & Return -->
          <div class="mt-12 flex flex-wrap items-center justify-center gap-3 sm:gap-4">
            <button
              type="button"
              @click="refreshHasil"
              :disabled="isRefreshing"
              class="px-6 py-3 rounded-xl font-bold text-sm bg-[#FFFDFB] dark:bg-[#27060F] text-[#3B0613] dark:text-[#FCCE88] border border-[#3B0613]/20 dark:border-[#FCCE88]/30 hover:bg-[#FAF7F2] dark:hover:bg-[#350914] transition-all shadow-md inline-flex items-center gap-2 active:scale-95 disabled:opacity-60 cursor-pointer"
              title="Perbarui Hasil Suara"
            >
              <MaterialIcon icon="refresh" :class="['text-xl', isRefreshing ? 'animate-spin' : '']" />
              <span>{{ isRefreshing ? 'Memperbarui...' : 'Perbarui Hasil' }}</span>
            </button>

            <Link
              href="/"
              class="px-7 py-3 rounded-xl font-bold text-sm bg-[#3B0613] dark:bg-[#FCCE88] text-white dark:text-[#2A050E] hover:bg-[#51071B] dark:hover:bg-[#E2B771] transition-all shadow-md inline-flex items-center gap-2 active:scale-95 cursor-pointer"
            >
              <MaterialIcon icon="arrow_back" class="text-xl" />
              <span>Kembali ke Beranda</span>
            </Link>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>