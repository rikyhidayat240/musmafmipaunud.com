<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import KotakSuaraIllustration from "@/components/KotakSuaraIllustration.vue";
import LoginModal from "@/components/LoginModal.vue";
import OrnamentDivider from "@/components/OrnamentDivider.vue";
import { BreadcrumbItem, Kegiatan } from "@/types";
import { Head, Link, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { ArrowRight, Lock, Vote, ShieldCheck, HelpCircle } from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

const props = defineProps<{
  kegiatan?: Kegiatan;
}>();

const page = usePage();
const auth = computed(() => page.props.auth);
const showLoginModal = ref(false);

const breadcrumbs: BreadcrumbItem[] = [{ title: "Beranda", href: "/" }];

// Countdown timer logic
const currentTime = ref(new Date());
let timerInterval: number | null = null;

const timeRemaining = computed(() => {
  if (!props.kegiatan) {
    return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true, label: "" };
  }

  const parseTarget = (raw?: string, formatted?: string) => {
    const val = raw || formatted;
    if (!val) return NaN;
    return new Date(val.includes("T") ? val : val.replace(" ", "T")).getTime();
  };

  const startTime = parseTarget(props.kegiatan.waktu_mulai_raw, props.kegiatan.waktu_mulai);
  const endTime = parseTarget(props.kegiatan.waktu_selesai_raw, props.kegiatan.waktu_selesai);
  const now = currentTime.value.getTime();

  let targetTime: number;
  let label = "Pemilihan Dimulai Dalam:";

  if (!isNaN(startTime) && now < startTime) {
    targetTime = startTime;
    label = "Pemilihan Dimulai Dalam:";
  } else if (!isNaN(endTime) && now < endTime) {
    targetTime = endTime;
    label = "Pemilihan Berakhir Dalam:";
  } else {
    return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true, label: "" };
  }

  const diff = targetTime - now;
  if (isNaN(diff) || diff <= 0) {
    return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true, label: "" };
  }

  return {
    days: Math.floor(diff / (1000 * 60 * 60 * 24)),
    hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
    minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
    seconds: Math.floor((diff % (1000 * 60)) / 1000),
    expired: false,
    label,
  };
});

const formatTime = (t: number) => t.toString().padStart(2, "0");

onMounted(() => {
  timerInterval = setInterval(() => {
    currentTime.value = new Date();
  }, 1000);

  // Check if URL parameter requested login modal
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("login") === "true") {
    showLoginModal.value = true;
  }
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});

const eventYear = computed(() => props.kegiatan?.tahun || dayjs().year());

const accordionItems = [
  {
    value: "item-1",
    title: "Apa saja syarat untuk mengikuti pemilihan?",
    content: `Anda harus terdaftar sebagai mahasiswa aktif program studi sarjana di lingkungan Fakultas Matematika dan Ilmu Pengetahuan Alam Universitas Udayana pada tahun ${dayjs().year()}.`,
  },
  {
    value: "item-2",
    title: "Bagaimana cara melakukan login akun?",
    content:
      "Gunakan Nomor Induk Mahasiswa (NIM) serta password yang telah didaftarkan atau diverifikasi oleh panitia pemilihan.",
  },
  {
    value: "item-3",
    title: "Apakah saya dapat mengubah pilihan saya setelah memilih?",
    content:
      'Tidak. Setelah Anda mengonfirmasi pilihan dengan menekan tombol "Ya, Yakin", suara Anda langsung terkunci dan tidak dapat diubah kembali.',
  },
  {
    value: "item-4",
    title: "Apakah kerahasiaan pilihan saya terjamin?",
    content:
      "Ya, sistem menggunakan enkripsi terstandar dan azas LUBERJURDIL. Identitas pemilih dan surat suara yang diberikan diproses secara terpisah dan anonim.",
  },
];
</script>

<template>
  <Head title="Beranda - MUSMA LM FMIPA" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="relative w-full min-h-screen bg-[#FAF7F2] text-[#3B0613] overflow-hidden flex flex-col justify-between">
      
      <!-- Top-Right Balinese Botanical Corner Foliage (Figma Asset) -->
      <img
        src="/image 63.png"
        alt=""
        class="absolute -top-10 -right-10 w-44 sm:w-72 lg:w-96 h-auto opacity-75 select-none pointer-events-none z-0"
      />

      <!-- Bottom-Left Balinese Botanical Corner Foliage (Figma Asset) -->
      <img
        src="/image 62.png"
        alt=""
        class="absolute -bottom-10 -left-10 w-44 sm:w-72 lg:w-96 h-auto opacity-75 select-none pointer-events-none z-0"
      />

      <!-- Center Background Subtle Watermark -->
      <img
        src="/logo 2026.png"
        alt=""
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[380px] sm:w-[540px] h-auto object-contain opacity-[0.035] select-none pointer-events-none z-0"
      />

      <!-- ===== HERO SECTION (Figma Node 251:800) ===== -->
      <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-8 pt-8 sm:pt-14 pb-12 sm:pb-20 w-full flex-1 flex flex-col justify-center">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
          
          <!-- Left Column: Typography & CTAs -->
          <div class="lg:col-span-7 flex flex-col items-start text-left">
            <!-- Eyebrow Tag -->
            <div class="flex items-center gap-3 mb-3 sm:mb-4">
              <div class="w-8 h-0.5 bg-[#DA8278]"></div>
              <span class="text-xs sm:text-sm font-bold tracking-[0.2em] uppercase text-[#3B0613]/80">
                SUARAMU, MASA DEPAN KITA
              </span>
            </div>

            <!-- Main Display Heading -->
            <h1 class="font-serif font-black text-5xl sm:text-7xl lg:text-8xl text-[#3B0613] tracking-tight leading-[0.95] mb-1">
              MUSMA
            </h1>
            <h2 class="font-serif italic font-normal text-3xl sm:text-5xl lg:text-6xl text-[#DA8278] leading-tight mb-4 sm:mb-6">
              LM FMIPA {{ eventYear }}
            </h2>

            <!-- Narrative Paragraph -->
            <p class="text-[#5A2A34] text-sm sm:text-lg max-w-xl leading-relaxed mb-6 sm:mb-8">
              Saatnya menentukan pemimpin yang akan membawa aspirasi dan perubahan bagi seluruh mahasiswa FMIPA periode {{ Number(eventYear) }}/{{ Number(eventYear) + 1 }}.
            </p>

            <!-- Countdown Timer (if countdown is still running) -->
            <div v-if="kegiatan && !timeRemaining.expired" class="mb-6 sm:mb-8 w-full max-w-md">
              <p class="text-xs font-bold uppercase tracking-wider text-[#9A6A72] mb-2">
                {{ timeRemaining.label }}
              </p>
              <div class="grid grid-cols-4 gap-2 sm:gap-4">
                <div
                  v-for="(val, label) in { Hari: timeRemaining.days, Jam: timeRemaining.hours, Menit: timeRemaining.minutes, Detik: timeRemaining.seconds }"
                  :key="label"
                  class="bg-[#FFFDFB] border border-[#3B0613]/15 rounded-xl sm:rounded-2xl py-2 sm:py-3 px-1 text-center shadow-sm"
                >
                  <span class="font-serif font-bold text-lg sm:text-2xl text-[#3B0613] block">
                    {{ formatTime(val) }}
                  </span>
                  <span class="text-[9px] sm:text-xs font-semibold text-[#9A6A72] uppercase tracking-wider block">
                    {{ label }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Call to Action Buttons -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 w-full sm:w-auto">
              <!-- Guest: Open Login Modal -->
              <button
                v-if="!auth.user"
                type="button"
                @click="showLoginModal = true"
                class="group px-6 sm:px-8 py-3.5 sm:py-4 rounded-full font-semibold text-sm sm:text-base text-white bg-[#3B0613] hover:bg-[#50071B] active:scale-[0.98] transition-all shadow-lg hover:shadow-xl inline-flex items-center justify-center gap-3 cursor-pointer"
              >
                <span>Masuk untuk memilih</span>
                <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
              </button>

              <!-- Authenticated: Go to Bilik Suara -->
              <Link
                v-else-if="kegiatan"
                :href="`/vote/${kegiatan.id}`"
                class="group px-6 sm:px-8 py-3.5 sm:py-4 rounded-full font-semibold text-sm sm:text-base text-white bg-[#3B0613] hover:bg-[#50071B] active:scale-[0.98] transition-all shadow-lg hover:shadow-xl inline-flex items-center justify-center gap-3"
              >
                <span>Masuk ke Bilik Suara</span>
                <ArrowRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
              </Link>

              <!-- No active election currently -->
              <div
                v-else
                class="px-6 py-3 rounded-2xl bg-[#FFFDFB] border border-[#3B0613]/15 text-sm text-[#7A4A52] shadow-sm text-center"
              >
                Jadwal pemilihan akan segera diumumkan panitia.
              </div>
            </div>

          </div>

          <!-- Right Column: 3D Kotak Suara & Floating Ballots (Figma Node 250:552) -->
          <div class="lg:col-span-5 flex justify-center lg:justify-end">
            <KotakSuaraIllustration :year="eventYear" />
          </div>

        </div>
      </section>

      <!-- ===== THREE PILLARS / VALUES SECTION ===== -->
      <section class="relative z-10 max-w-6xl mx-auto px-4 sm:px-8 py-10 w-full">
        <OrnamentDivider size="sm" class="mb-8" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-[#FFFDFB] border border-[#3B0613]/15 rounded-3xl p-6 sm:p-7 shadow-sm flex flex-col items-start text-left hover:shadow-md transition-shadow">
            <div class="size-12 rounded-2xl bg-[#3B0613]/10 flex items-center justify-center mb-4 text-[#3B0613]">
              <Lock class="w-6 h-6" />
            </div>
            <h3 class="font-serif font-bold text-lg text-[#3B0613] mb-1.5">
              Rahasia &amp; Terenkripsi
            </h3>
            <p class="text-xs sm:text-sm text-[#5A2A34] leading-relaxed">
              Pilihan suara Anda dienkripsi dan dijamin kerahasiaannya dengan sistem integritas satu mahasiswa satu suara.
            </p>
          </div>

          <div class="bg-[#FFFDFB] border border-[#3B0613]/15 rounded-3xl p-6 sm:p-7 shadow-sm flex flex-col items-start text-left hover:shadow-md transition-shadow">
            <div class="size-12 rounded-2xl bg-[#3B0613]/10 flex items-center justify-center mb-4 text-[#3B0613]">
              <Vote class="w-6 h-6" />
            </div>
            <h3 class="font-serif font-bold text-lg text-[#3B0613] mb-1.5">
              Bilik Suara Mandiri
            </h3>
            <p class="text-xs sm:text-sm text-[#5A2A34] leading-relaxed">
              Pemberian suara dapat diakses secara fleksibel dan transparan dari perangkat mana saja selama sesi pemungutan suara berlangsung.
            </p>
          </div>

          <div class="bg-[#FFFDFB] border border-[#3B0613]/15 rounded-3xl p-6 sm:p-7 shadow-sm flex flex-col items-start text-left hover:shadow-md transition-shadow">
            <div class="size-12 rounded-2xl bg-[#3B0613]/10 flex items-center justify-center mb-4 text-[#3B0613]">
              <ShieldCheck class="w-6 h-6" />
            </div>
            <h3 class="font-serif font-bold text-lg text-[#3B0613] mb-1.5">
              Luber &amp; Jurdil
            </h3>
            <p class="text-xs sm:text-sm text-[#5A2A34] leading-relaxed">
              Berdasarkan asas Langsung, Umum, Bebas, Rahasia, Jujur, dan Adil untuk suksesi demokrasi mahasiswa FMIPA.
            </p>
          </div>
        </div>
      </section>

      <!-- ===== FAQ ACCORDION SECTION ===== -->
      <section class="relative z-10 max-w-4xl mx-auto px-4 sm:px-8 py-10 w-full">
        <div class="text-center mb-6">
          <span class="text-xs font-bold uppercase tracking-[0.2em] text-[#DA8278]">
            Informasi Pemilih
          </span>
          <h2 class="font-serif font-bold text-2xl sm:text-3xl text-[#3B0613] mt-1">
            Pertanyaan yang Sering Diajukan
          </h2>
        </div>

        <div class="bg-[#FFFDFB] border border-[#3B0613]/15 rounded-3xl p-6 sm:p-8 shadow-sm">
          <Accordion type="single" collapsible class="w-full">
            <AccordionItem
              v-for="item in accordionItems"
              :key="item.value"
              :value="item.value"
              class="border-b border-[#3B0613]/10 last:border-b-0 py-1"
            >
              <AccordionTrigger class="text-left font-serif font-semibold text-[#3B0613] text-sm sm:text-base hover:text-[#50071B] hover:no-underline">
                {{ item.title }}
              </AccordionTrigger>
              <AccordionContent class="text-xs sm:text-sm text-[#5A2A34] leading-relaxed pt-1 pb-3">
                {{ item.content }}
              </AccordionContent>
            </AccordionItem>
          </Accordion>
        </div>
      </section>

      <!-- Login Modal Overlay (Figma Node 251:936) -->
      <LoginModal
        :open="showLoginModal"
        :year="eventYear"
        @close="showLoginModal = false"
      />

    </div>
  </AppLayout>
</template>
