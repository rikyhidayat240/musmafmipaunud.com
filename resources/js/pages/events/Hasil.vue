<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Kegiatan } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
  kegiatan?: Kegiatan | null
}>()

const totalSuara = props.kegiatan?.kandidat?.reduce((acc, val) => acc + val.jumlah_suara, 0) || 0;
const kandidat = computed(() => {
  return props.kegiatan?.kandidat?.map(calon => ({
    ...calon,
    jumlah_suara_persen: totalSuara > 0 ? parseFloat((calon.jumlah_suara / totalSuara * 100).toFixed(2)) : 0
  }))
})
</script>
<template>

  <Head title="Hasil Pemilihan" />
  <AppLayout>
    <div class="h-full mb-8">
      <div class="w-full flex justify-between items-start relative mb-6 sm:mb-8">
        <img src="/assets/corner-image-left.png" alt="" class="w-16 sm:w-32 md:w-40 lg:w-50">
        <img src="/assets/background-logo-dpm.png" alt="" class="h-16 sm:h-32 md:h-40 lg:h-50 my-auto">
        <img src="/assets/corner-image-right.png" alt="" class="w-16 sm:w-32 md:w-40 lg:w-50">
        <h1
          class="text-base sm:text-xl md:text-2xl lg:text-4xl font-bold text-center text-white uppercase absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 px-4 max-w-full"
          style="text-shadow:
   -2px -2px 0 #A50000,  
    2px -2px 0 #A50000,
    -2px 2px 0 #A50000,
     2px 2px 0 #A50000;">{{ kegiatan && kegiatan.kandidat && kegiatan.kandidat.length > 0
      ? kegiatan.nama
      : "Tidak ada event pemilihan berlangsung" }}
        </h1>
      </div>

      <div v-if="kegiatan && kegiatan.kandidat && kegiatan.kandidat.length > 0">
        <div class="flex flex-wrap px-2 sm:px-4 gap-4 md:gap-0 justify-center">
          <div 
            class="flex flex-col justify-between w-full sm:w-1/2 md:w-1/2 mb-4 md:mb-0" 
            v-for="(calon, i) in kandidat"
            :key="calon.id"
            :class="(i + 1) == 3 ? 'md:!w-fit mx-auto' : ((i + 1) % 2 == 0 ? 'items-center md:items-start' : 'items-center md:items-end md:pr-8')">
            
            <!-- Layout untuk Mobile (< md) -->
            <div class="flex md:hidden flex-col items-center gap-3 w-full">
              <!-- Foto dan Bar Persentase -->
              <div class="flex items-end gap-3">
                <!-- Foto Kandidat -->
                <div class="h-56 overflow-hidden rounded-b-full flex flex-col justify-end w-36 shrink-0">
                  <div class="h-56 w-full mt-auto rounded-full bg-linear-to-b from-[#F5791A] to-[#B42323] flex flex-col justify-end">
                    <img 
                      :src="`/storage/${calon.foto}`" 
                      class="h-auto max-h-full w-auto mx-auto relative top-8" 
                      alt="">
                  </div>
                </div>
                
                <!-- Bar Persentase -->
                <div class="h-56 w-10 flex flex-col gap-2 shrink-0 justify-end">
                  <div class="font-semibold text-center w-full text-sm">
                    {{ calon.jumlah_suara_persen }}%
                  </div>
                  <div 
                    class="rounded-t-2xl transition-all duration-500" 
                    :class="i % 2 == 0 ? 'bg-accent-red' : 'bg-accent-orange'"
                    :style="{ height: `${calon.jumlah_suara_persen}%` }">
                  </div>
                </div>
              </div>
              
              <!-- Nama dan Info Kandidat (di bawah foto) -->
              <div class="flex flex-col items-center text-center">
                <h3 class="text-base sm:text-lg font-semibold">
                  {{ calon.mahasiswa![0].nama }}
                </h3>
                <small class="text-xs sm:text-sm">
                  {{ calon.mahasiswa![0].program_studi.nama }} 
                  {{ calon.mahasiswa![0].angkatan.toString().substring(2, 4) }}
                </small>
              </div>
            </div>

            <!-- Layout untuk Tablet & Desktop (>= md) -->
            <div 
              class="hidden md:flex h-120 items-end gap-4 w-full"
              :class="(i + 1) % 2 == 0 ? 'flex-row-reverse' : ((i + 1) == 3 ? 'flex-row-reverse' : '')">
              
              <!-- Nama dan Info Kandidat -->
              <div 
                class="h-80 flex flex-col justify-center"
                :class="(i + 1) == 3 ? 'text-start' : ((i + 1) % 2 == 1 ? 'text-end w-full' : 'w-full')">
                <h3 class="text-lg lg:text-xl font-semibold">
                  {{ calon.mahasiswa![0].nama }}
                </h3>
                <small class="text-sm">
                  {{ calon.mahasiswa![0].program_studi.nama }} 
                  {{ calon.mahasiswa![0].angkatan.toString().substring(2, 4) }}
                </small>
              </div>
              
              <!-- Foto Kandidat -->
              <div class="h-full overflow-hidden rounded-b-full flex flex-col justify-end w-50 shrink-0">
                <div class="h-80 w-full mt-auto rounded-full bg-linear-to-b from-[#F5791A] to-[#B42323] flex flex-col justify-end">
                  <img 
                    :src="`/storage/${calon.foto}`" 
                    class="h-auto mx-auto relative top-10" 
                    alt="">
                </div>
              </div>
              
              <!-- Bar Persentase -->
              <div class="h-full w-12 flex flex-col gap-2 shrink-0 justify-end">
                <div class="font-semibold text-center w-full">
                  {{ calon.jumlah_suara_persen }}%
                </div>
                <div 
                  class="rounded-t-2xl transition-all duration-500" 
                  :class="i % 2 == 0 ? 'bg-accent-red' : 'bg-accent-orange'"
                  :style="{ height: `${calon.jumlah_suara_persen}%` }">
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="text-center text-primary font-semibold text-base sm:text-lg mt-6 md:mt-8">
          Total Suara : {{ totalSuara }}
        </div>
      </div>
    </div>
  </AppLayout>
</template>