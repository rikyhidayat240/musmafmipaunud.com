<script setup lang="ts">
import { Kandidat, Kegiatan } from '@/types';
import { Head, router } from "@inertiajs/vue3";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { CheckCircle2, LoaderCircle, Target, Users } from 'lucide-vue-next';
import { Separator } from '@/components/ui/separator';
import { disable } from '@/routes/two-factor';
import dayjs from 'dayjs';

const props = defineProps<{
  kegiatan: Kegiatan
}>()

const isVote = ref(false)
const isProcessing = ref(false)

watch(isVote, () => {
  window.scrollTo({
    top: 0,
  })
})

const selectedKandidat = ref<Kandidat | null>(null);

function vote() {
  if (selectedKandidat.value) {
    isProcessing.value = true;
    router.post('', {
      kandidat: selectedKandidat.value?.id
    })
  }
}

const formatMisi = (misi: string): string[] => {
  const misiItems = misi.split(/\d+\.\s/).filter(item => item.trim());
  return misiItems.map(item => item.trim());
}
</script>
<template>

  <Head title="Vote" />

  <AppLayout>
    <div class="h-full pb-16">
      <div class="w-full flex justify-between items-start relative">
        <img src="/assets/corner-image-left.png" alt="" class="w-20 sm:w-40 lg:w-50">
        <img src="/assets/background-logo-dpm.png" alt="" class="h-20 sm:h-50 my-auto">
        <img src="/assets/corner-image-right.png" alt="" class="w-20 sm:w-40 lg:w-50">
        <h1
          class="text-xl sm:text-3xl lg:text-4xl font-bold text-center text-primary uppercase absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2"
          style="text-shadow: -2px -2px 0 #FFFFFF, 2px -2px 0 #FFFFFF, -2px 2px 0 #FFFFFF, 2px 2px 0 #FFFFFF;">
          {{ kegiatan.nama }}
        </h1>
      </div>

      <div class="" v-if="!isVote">
        <div class="flex h-full flex-col gap-2 overflow-x-auto">
          <div class="relative flex mb-12 flex-1 flex-col items-start justify-start">
            <div class="mx-auto mt-12 grid w-full max-w-7xl items-start gap-4 px-4">
              <h1 class="md:text-md font-semibold lg:text-xl">Syarat & Ketentuan :</h1>
              <ol class="md:text-md ml-2 list-inside list-decimal space-y-2 text-sm md:ml-10 lg:text-lg">
                <li>Mahasiswa/i yang dapat memilih adalah mahasiswa/i aktif Fakultas MIPA yang terdaftar sebagai calon delegasi DPM FMIPA periode {{ dayjs().year() + 1 }}.</li>
                <li>Mahasiswa/i hanya bisa melakukan pemilihan sebanyak satu kali tanpa adanya pengulangan maupun perubahan pilihan.</li>
                <li>Mahasiswa/i diharapkan memilih dengan berlandaskan Luberjurdil (Langsung, Umum, Bebas, Rahasia, Jujur, dan Adil).</li>
                <li>Hasil pemilihan bersifat mutlak dan tidak dapat diganggu gugat, sesuai dengan aturan yang telah ditetapkan.</li>
                <li>Setiap pelanggaran terhadap syarat dan ketentuan ini akan dikenakan sanksi sesuai dengan peraturan yang berlaku.</li>
              </ol>
            </div>
          </div>

          <!-- Kandidat Cards -->
          <div class="flex flex-col gap-4 mx-auto w-full max-w-7xl px-4 py-12 space-y-8">
            <h1 class="md:text-xl font-bold lg:text-2xl text-center">Kandidat {{ kegiatan.nama }}</h1>
            <Card v-for="(k, index) in kegiatan.kandidat" :key="k.id"
              class="overflow-hidden hover:shadow-xl transition-all duration-300 border-2 py-0"
              :class="index % 2 === 0 ? 'hover:border-primary/50' : 'hover:border-secondary/50'">

              <div class="grid grid-cols-1 lg:grid-cols-12 items-start gap-0">
                <!-- Foto Section -->
                <div :class="`lg:col-span-4 relative ${index % 2 !== 0 ? 'lg:order-last' : ''}`">
                  <!-- Mobile View - Full Image with Overlay -->
                  <div class="relative h-64 min-h-[400px] lg:hidden">
                    <img :src="k.foto !== null ? `/storage/${k.foto}` : '/assets/blank-profile-picture.webp'"
                      alt="Foto Kandidat"
                      class="absolute inset-0 w-[80%] mx-auto h-full object-cover md:object-contain object-top" />

                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent">
                    </div>

                    <!-- Nomor Urut Badge - Mobile -->
                    <div class="absolute top-4 left-4 z-10">
                      <div
                        class="bg-primary text-primary-foreground px-4 py-2 rounded-full font-bold text-lg shadow-xl flex items-center gap-1.5">
                        <span class="text-xs opacity-75">No.</span>
                        <span>{{ k.no_urut }}</span>
                      </div>
                    </div>

                    <!-- Nama di Mobile (overlay di foto) -->
                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                      <h2 class="text-xl font-bold mb-1">
                        <span v-for="(mhs, idx) in k.mahasiswa" :key="mhs.nim">
                          {{ mhs.nama }}
                          <span v-if="idx < k.mahasiswa!.length - 1"> & </span>
                        </span>
                      </h2>
                      <p class="text-sm opacity-90">
                        <span v-for="(mhs, idx) in k.mahasiswa" :key="mhs.nim">
                          {{ mhs.nim }}
                          <span v-if="idx < k.mahasiswa!.length - 1"> | </span>
                        </span>
                      </p>
                    </div>
                  </div>

                  <!-- Desktop View - Circular Avatar -->
                  <div class="hidden lg:flex lg:flex-col lg:items-center lg:justify-center lg:p-10 lg:h-full relative">
                    <div class="relative">
                      <!-- Circular Photo -->
                      <div class="size-72 rounded-full overflow-hidden border-4 border-primary/20 shadow-xl">
                        <img :src="k.foto !== null ? `/storage/${k.foto}` : '/assets/blank-profile-picture.webp'"
                          alt="Foto Kandidat" class="w-[90%] mx-auto h-full object-cover object-top" />
                      </div>

                      <!-- Nomor Urut Badge - Desktop (Floating on Avatar) -->
                      <div class="absolute -top-2 -right-2">
                        <div
                          class="bg-primary text-primary-foreground px-3 py-1.5 rounded-full font-bold text-sm shadow-lg flex items-center gap-1">
                          <span class="text-xs opacity-75">No.</span>
                          <span>{{ k.no_urut }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Content Section -->
                <div class="lg:col-span-8 p-6 md:p-8 lg:p-10">
                  <!-- Header - Hidden di mobile karena ada di foto -->
                  <div class="hidden lg:block mb-6">
                    <div class="flex items-start justify-between mb-4">
                      <div>
                        <h2 class="text-2xl font-bold mb-2 text-foreground">
                          <span v-for="(mhs, idx) in k.mahasiswa" :key="mhs.nim">
                            {{ mhs.nama }}
                            <span v-if="idx < k.mahasiswa!.length - 1"> & </span>
                          </span>
                        </h2>
                        <div class="flex flex-wrap gap-2">
                          <Badge v-for="(mhs, idx) in k.mahasiswa" :key="mhs.nim" variant="secondary" class="text-sm">
                            <Users class="w-3 h-3 mr-1" />
                            {{ mhs.nim }} - Calon {{ mhs.pivot.jabatan.charAt(0).toUpperCase() +
                              mhs.pivot.jabatan.slice(1) }}
                          </Badge>
                        </div>
                      </div>
                    </div>
                    <Separator class="my-4" />
                  </div>

                  <!-- Visi Section -->
                  <div class="mb-8">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="p-2 bg-primary/10 rounded-lg">
                        <Target class="w-5 h-5 text-primary" />
                      </div>
                      <h3 class="text-xl font-bold text-primary">Visi</h3>
                    </div>
                    <p class="text-base leading-relaxed text-muted-foreground lg:pl-11">
                      {{ k.visi }}
                    </p>
                  </div>

                  <!-- Misi Section -->
                  <div>
                    <div class="flex items-center gap-2 mb-4">
                      <div class="p-2 bg-primary/10 rounded-lg">
                        <CheckCircle2 class="w-5 h-5 text-primary" />
                      </div>
                      <h3 class="text-xl font-bold text-primary">Misi</h3>
                    </div>
                    <ol class="space-y-3 lg:pl-10">
                      <li v-for="(misiItem, idx) in formatMisi(k.misi)" :key="idx"
                        class="flex items-start gap-3 text-base text-muted-foreground">
                        <span
                          class="shrink-0 w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center text-sm font-semibold">
                          {{ idx + 1 }}
                        </span>
                        <span class="leading-relaxed">{{ misiItem }}</span>
                      </li>
                    </ol>
                  </div>
                </div>
              </div>
            </Card>
          </div>
          <div class="text-center">
            <Button size="lg" @click="isVote = true" class="cursor-pointer">Vote Sekarang</Button>
          </div>
        </div>
      </div>

      <!-- Tampilan Vote -->
      <div v-if="isVote">
        <div class="flex flex-wrap px-4">
          <div class="h-120 flex flex-col justify-between w-full sm:w-1/2" v-for="(kandidat, i) in kegiatan.kandidat"
            :key="kandidat.id"
            :class="(i + 1) == 3 ? '!w-fit mx-auto' : ((i + 1) % 2 == 0 ? 'items-center sm:items-start' : 'items-center sm:items-end sm:pr-8')">
            <div class="h-105 flex items-end gap-4"
              :class="(i + 1) % 2 == 0 ? 'flex-row-reverse' : ((i + 1) == 3 ? 'sm:flex-row-reverse' : '')">
              <div class="h-80 flex flex-col justify-center"
                :class="(i + 1) == 3 ? 'text-start' : ((i + 1) % 2 == 1 ? 'text-end w-full' : 'w-full')">
                <h3 class="text-xl font-semibold">
                  {{ kandidat.mahasiswa![0].nama }}
                </h3>
                <small>{{ kandidat.mahasiswa![0].program_studi.nama }} {{
                  kandidat.mahasiswa![0].angkatan.toString().substring(2, 4) }}</small>
              </div>
              <div class="h-full overflow-hidden rounded-b-full flex flex-col justify-end w-50 shrink-0">
                <div
                  class="h-80 w-full mt-auto rounded-full bg-linear-to-b from-[#F5791A] to-[#B42323] flex flex-col justify-end">
                  <img :src="'/storage/' + kandidat.foto" class="h-auto mx-auto relative top-10" alt="">
                </div>
              </div>
            </div>
            <div class="flex"
              :class="(i + 1) == 3 ? 'justify-center' : ((i + 1) % 2 == 1 ? 'justify-end' : 'justify-start')">
              <Button class="w-50" @click="selectedKandidat = kandidat">Vote</Button>
            </div>
          </div>
        </div>
        <Dialog :open="!!selectedKandidat"
          @update:open="(open) => selectedKandidat = (!open ? null : selectedKandidat)">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Vote Calon Ketua</DialogTitle>
              <DialogDescription class="mt-2">
                Anda yakin ingin memilih calon ketua nomor urut {{ selectedKandidat?.no_urut }}?
              </DialogDescription>
            </DialogHeader>

            <DialogFooter>
              <Button variant="outline" @click="selectedKandidat = null">Batal</Button>
              <Button @click="vote" :disabled="isProcessing">
                <LoaderCircle v-if="isProcessing" class="h-4 w-4 animate-spin" />
                Vote
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  </AppLayout>


</template>