<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Kegiatan } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import dayjs from 'dayjs';

const props = defineProps<{
  kegiatan?: Kegiatan | null
}>()

const totalSuara = props.kegiatan?.kandidat?.reduce((acc, val) => acc + val.jumlah_suara, 0) || 0;
const kandidat = computed(() => {
  return props.kegiatan?.kandidat?.map(calon => ({
    ...calon,
    jumlah_suara_persen: totalSuara > 0 ? parseFloat((calon.jumlah_suara / totalSuara * 100).toFixed(2)) : 0
  })) || []
})
</script>

<template>
  <Head title="Hasil Pemilihan" />
  <AppLayout>
    <div class="min-h-full" style="background-color:#F5EFE6;">

      <!-- ===== HERO HEADER ===== -->
      <div class="relative w-full overflow-hidden" style="background: linear-gradient(135deg, #51071B 0%, #3B0312 100%); min-height: 180px;">
        <!-- Ornaments -->
        <img src="/image 62.png" class="absolute top-0 left-0 w-32 md:w-52 h-auto object-contain opacity-25 rotate-180 select-none pointer-events-none" alt="" />
        <img src="/image 63.png" class="absolute top-0 right-0 w-20 md:w-36 h-auto object-contain opacity-25 rotate-180 select-none pointer-events-none" alt="" />
        <img src="/image 61.png" class="absolute bottom-0 left-1/2 -translate-x-1/2 h-28 w-auto opacity-10 select-none pointer-events-none" alt="" />

        <!-- Content -->
        <div class="relative z-10 flex flex-col items-center justify-center py-10 px-4 text-center">
          <img src="/logo 2026.png" alt="Logo" class="h-16 w-auto object-contain mb-3 opacity-90" />
          <h1 class="text-2xl md:text-4xl font-bold text-white tracking-wide uppercase"
              style="font-family:'Playfair Display',serif; text-shadow: 0 2px 8px rgba(0,0,0,0.3);">
            {{ kegiatan && kegiatan.kandidat && kegiatan.kandidat.length > 0 ? kegiatan.nama : 'Hasil Pemilihan' }}
          </h1>
          <div class="flex items-center gap-3 mt-2">
            <div class="h-px w-20" style="background:linear-gradient(90deg,transparent,rgba(235,206,103,0.7));"></div>
            <span style="color:#EBCE67;" class="text-sm font-semibold tracking-widest uppercase">MUSMA LM FMIPA {{ dayjs().year() }}</span>
            <div class="h-px w-20" style="background:linear-gradient(90deg,rgba(235,206,103,0.7),transparent);"></div>
          </div>
        </div>
      </div>

      <!-- ===== CONTENT ===== -->
      <div class="max-w-7xl mx-auto px-4 pt-10 pb-16">

        <!-- No event state -->
        <div v-if="!kegiatan || !kegiatan.kandidat || kegiatan.kandidat.length === 0"
          class="flex flex-col items-center justify-center py-24 text-center"
          style="background:#FFF8F8; border:1px solid #E3D7D9; border-radius:16px; margin-top:8px;">
          <img src="/image 61.png" class="w-28 h-auto opacity-30 mb-5" alt="" />
          <p class="text-xl font-bold musma-section-title">Tidak ada kegiatan pemilihan yang berlangsung</p>
          <p class="text-sm mt-2" style="color:#9A6A72;">Hasil akan ditampilkan setelah pemilihan selesai.</p>
        </div>

        <div v-if="kegiatan && kegiatan.kandidat && kegiatan.kandidat.length > 0">

          <!-- Total suara -->
          <div class="flex justify-center mb-12">
            <div class="text-center px-12 py-5 shadow-lg"
                 style="background:linear-gradient(135deg,#51071B,#8B1A2E); border-radius:16px;">
              <p class="text-xs font-bold tracking-widest uppercase mb-1" style="color:rgba(235,206,103,0.7);">Total Suara Masuk</p>
              <p class="text-5xl font-bold" style="color:#EBCE67; font-family:'Playfair Display',serif;">{{ totalSuara }}</p>
              <p class="text-xs mt-1" style="color:rgba(245,239,230,0.5);">dari keseluruhan pemilih</p>
            </div>
          </div>

          <!-- Kandidat grid -->
          <div class="flex flex-col md:flex-row gap-8 justify-center items-stretch">
            <div
              v-for="(calon, i) in kandidat"
              :key="calon.id"
              class="flex-1"
              style="min-width: 280px; max-width: 480px;"
            >
              <!-- Main card -->
              <div class="relative overflow-hidden shadow-xl h-full flex flex-col"
                   style="background:#FFF8F8; border-radius:20px; border:2px solid #E3D7D9;">

                <!-- Top gradient accent -->
                <div class="h-2 w-full" :style="{
                  background: i % 2 === 0
                    ? 'linear-gradient(90deg, #51071B, #8B1A2E)'
                    : 'linear-gradient(90deg, #C5AA6C, #EBCE67)'
                }"></div>

                <!-- Number badge -->
                <div class="absolute top-6 left-6 w-12 h-12 rounded-full flex items-center justify-center shadow-lg z-10"
                     :style="{
                       background: i % 2 === 0 ? 'linear-gradient(135deg,#51071B,#8B1A2E)' : 'linear-gradient(135deg,#C5AA6C,#EBCE67)',
                       color: i % 2 === 0 ? '#EBCE67' : '#51071B',
                     }">
                  <span class="font-black text-lg">{{ String(calon.no_urut).padStart(2,'0') }}</span>
                </div>

                <!-- Photo section -->
                <div class="flex justify-center items-end pt-8 pb-0 relative"
                     style="min-height: 280px; background: linear-gradient(180deg, #F5ECEE 0%, #FFF8F8 100%);">
                  <!-- Decorative circle behind photo -->
                  <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-52 h-52 rounded-full opacity-30"
                       :style="{
                         background: i % 2 === 0 ? 'radial-gradient(circle,#51071B,transparent)' : 'radial-gradient(circle,#C5AA6C,transparent)'
                       }"></div>
                  <!-- Photo -->
                  <img
                    :src="`/storage/${calon.foto}`"
                    class="relative z-10 w-auto h-64 object-cover object-top"
                    style="max-width: 100%;"
                    alt="Foto Kandidat"
                  />
                </div>

                <!-- Info section -->
                <div class="px-6 pt-5 pb-4 flex-1 flex flex-col">
                  <div class="text-center mb-4">
                    <h3 class="text-xl font-bold musma-section-title mb-1">{{ calon.mahasiswa?.[0]?.nama || 'Kandidat' }}</h3>
                    <p class="text-sm" style="color:#9A6A72;">
                      {{ calon.mahasiswa?.[0]?.program_studi?.nama || '' }}
                      {{ calon.mahasiswa?.[0]?.angkatan?.toString().substring(2, 4) || '' }}
                    </p>
                  </div>

                  <!-- Divider -->
                  <div class="flex items-center gap-3 mb-4">
                    <div class="flex-1 h-px" :style="{background: i%2===0 ? 'rgba(81,7,27,0.15)' : 'rgba(197,170,108,0.4)'}"></div>
                    <span :style="{color: i%2===0 ? '#C5AA6C' : '#51071B'}" class="text-sm font-bold">✦</span>
                    <div class="flex-1 h-px" :style="{background: i%2===0 ? 'rgba(81,7,27,0.15)' : 'rgba(197,170,108,0.4)'}"></div>
                  </div>

                  <!-- Vote count big -->
                  <div class="text-center mb-5">
                    <p class="text-6xl font-black musma-section-title">{{ calon.jumlah_suara }}</p>
                    <p class="text-sm font-medium" style="color:#9A6A72;">suara</p>
                  </div>

                  <!-- Bar chart -->
                  <div class="mt-auto">
                    <div class="flex items-center justify-between mb-2">
                      <span class="text-xs font-semibold" style="color:#9A6A72;">Persentase suara</span>
                      <span class="text-xl font-black" :style="{color: i%2===0 ? '#51071B' : '#C5AA6C'}">
                        {{ calon.jumlah_suara_persen }}%
                      </span>
                    </div>
                    <!-- Bar -->
                    <div class="w-full h-5 rounded-full overflow-hidden" style="background-color:#E3D7D9;">
                      <div
                        class="h-full rounded-full transition-all duration-1000 ease-out"
                        :style="{
                          width: `${calon.jumlah_suara_persen}%`,
                          background: i % 2 === 0
                            ? 'linear-gradient(90deg, #51071B, #8B1A2E)'
                            : 'linear-gradient(90deg, #B8935A, #EBCE67)'
                        }"
                      ></div>
                    </div>
                  </div>
                </div>

                <!-- Bottom gradient accent -->
                <div class="h-1 w-full" :style="{
                  background: i % 2 === 0
                    ? 'linear-gradient(90deg, #51071B, #8B1A2E)'
                    : 'linear-gradient(90deg, #C5AA6C, #EBCE67)'
                }"></div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>