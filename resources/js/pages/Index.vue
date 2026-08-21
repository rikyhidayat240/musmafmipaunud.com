<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Kegiatan } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion"
import { Card, CardHeader, CardContent, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import Autoplay from "embla-carousel-autoplay";
import { Carousel, CarouselContent, CarouselItem } from "@/components/ui/carousel"

const plugin = Autoplay({
  delay: 2000,
  stopOnMouseEnter: true,
  stopOnInteraction: false,
})

const props = defineProps<{
    kegiatan?: Kegiatan
}>()

// Countdown timer logic
const currentTime = ref(new Date());
let interval: number | null = null;

const timeRemaining = computed(() => {
  const target = new Date(props.kegiatan?.waktu_mulai_raw ?? '');
  const now = currentTime.value;
  const diff = target.getTime() - now.getTime();

    if (diff <= 0) {
        return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    return { days, hours, minutes, seconds, expired: false };
});

// Helper function to format time values
const formatTime = (time: number) => {
    return time.toString().padStart(2, '0');
};

// Set up interval to update current time every second
onMounted(() => {
    interval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (interval) {
        clearInterval(interval);
    }
});

// Accordion FAQ data
const defaultValue = "item-1"
const accordionItems = [
  { value: "item-1", title: "Apa saja syarat untuk mengikuti pemilihan?", content: `Anda harus terdaftar sebagai calon fungsionaris DPM FMIPA periode ${dayjs().year() + 1} dan mahasiswa aktif di fakultas dan program studi yang sesuai pada tahun ${dayjs().year()}.` },
  { value: "item-2", title: "Bagaimana cara melakukan registrasi akun?", content: "Anda cukup menyiapkan NIM dan nama lengkap yang sesuai dengan profil pada laman IMISSU. Kemudian, ikuti langkah-langkah yang terdapat pada laman registrasi akun." },
  { value: "item-3", title: "Bagaimana cara login ke dalam laman pemilihan?", content: "Anda dapat melakukan login dengan menggunakan email dan kata sandi yang telah Anda daftarkan sebelumnya. Pastikan email yang terdaftar merupakan email aktif dari universitas." },
  { value: "item-4", title: "Bagaimana jika saya lupa kata sandi?", content: "Anda dapat melakukan reset kata sandi melalui laman login dengan mengklik tautan 'Lupa kata sandi?'. Ikuti langkah-langkah yang diberikan untuk mengatur ulang kata sandi Anda." },
  { value: "item-5", title: "Kegiatan apa saja yang dapat saya ikuti?", content: `Anda dapat mengikuti kegiatan pemilihan calon ketua DPM FMIPA periode ${dayjs().year() + 1}. Sebelum Anda melakukan pemilihan, Anda juga dapat melihat rincian profil serta visi misi dari calon ketua.` },
  { value: "item-6", title: "Bagaimana tata cara melakukan pemilihan?", content: "Anda diharuskan untuk memiliki akun dan login terlebih dahulu untuk dapat melakukan pemilihan. Setelah Anda login dan memastikan syarat di atas terpenuhi, Anda dapat memulai proses pemilihan. Anda harus menyelesaikan semua kegiatan pemilihan sebelum Anda dapat keluar dari situs pemilihan." },
  { value: "item-7", title: "Apakah saya dapat mengubah pilihan saya setelah memilih?", content: "Tidak, setelah Anda mengklik tombol 'Vote' pada halaman pemilihan, pilihan Anda akan terkunci dan tidak dapat diubah. Anda juga hanya memiliki kesempatan sekali saja untuk melakukan pemilihan." },
  { value: "item-8", title: "Bagaimana cara memperbarui profil dan akun?", content: "Anda dapat memperbarui profil dan akun Anda melalui halaman pengaturan akun. Anda juga dapat mengubah kata sandi Anda di halaman ini. Pastikan untuk menyimpan segala perubahan yang telah Anda buat." },
]

const page = usePage();
const auth = computed(() => page.props.auth);
const title = auth.value.user ? 'Beranda' : 'Selamat Datang';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Beranda',
        href: '/dashboard',
    },
];

const heroImages = [
  '/assets/foto-slide-hero/Banner_1.webp',
  '/assets/foto-slide-hero/Banner_2.webp',
  '/assets/foto-slide-hero/Banner_3.webp',
  '/assets/foto-slide-hero/Banner_4.webp',
  '/assets/foto-slide-hero/Banner_5.webp',
  '/assets/foto-slide-hero/Banner_6.webp',
  '/assets/foto-slide-hero/Banner_7.webp',
  '/assets/foto-slide-hero/Banner_8.webp',
  '/assets/foto-slide-hero/Banner_9.webp',
]
</script>
<template>

  <Head :title="title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-8 overflow-hidden">
      <!-- Countdown timer -->
      <div class="relative min-h-[90vh] flex flex-1 justify-center items-center">
        <!-- Background Carousel -->
        <Carousel class="absolute w-full h-full saturate-0 md:saturate-0 md:backdrop-blur" :plugins="[plugin]"
          @mouseenter="plugin.stop" @mouseleave="[plugin.reset(), plugin.play()]">
          <CarouselContent>
            <CarouselItem v-for="image in heroImages" :key="image">
              <div class="flex items-center justify-center h-full">
                <Card class="w-full border-0 py-0">
                  <CardContent class="flex items-center justify-center px-0">
                    <img :src="image" alt="Placeholder" class="w-full h-[90vh] object-cover" />
                  </CardContent>
                </Card>
              </div>
            </CarouselItem>
          </CarouselContent>
        </Carousel>

        <!-- Content with countdown timer -->
        <div class="relative z-10 px-4 space-y-4 md:space-y-6 flex flex-col items-center justify-center"
          v-if="kegiatan">
          <!-- Header -->
          <div class="stroke-black-500 text-center text-shadow-md text-shadow-foreground/30">
            <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-white mb-2">
              Musyawarah Mahasiswa FMIPA
            </h2>
            <p class="text-white font-medium text-sm md:text-base max-w-lg">
              Pemilihan Ketua Dewan Perwakilan Mahasiswa Fakultas Matematika dan Ilmu Pengetahuan Alam tahun {{
                dayjs().year() + 1 }} akan dimulai dalam
            </p>
          </div>

          <!-- Countdown Display -->
          <div class="flex justify-center space-x-4 items-start text-shadow-md text-shadow-foreground/30">
            <!-- Days -->
            <div class="text-center">
              <p class="text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                {{ formatTime(timeRemaining.days) }}
              </p>
              <p class="text-sm lg:text-base text-white font-medium mt-2">
                Hari
              </p>
            </div>

            <div class="pt-2 text-xl md:text-2xl lg:text-3xl font-bold text-white">:</div>

            <!-- Hours -->
            <div class="text-center">
              <p class="text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                {{ formatTime(timeRemaining.hours) }}
              </p>
              <p class="text-sm lg:text-base text-white font-medium mt-2">
                Jam
              </p>
            </div>

            <div class="pt-2 text-xl md:text-2xl lg:text-3xl font-bold text-white">:</div>

            <!-- Minutes -->
            <div class="text-center">
              <p class="text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                {{ formatTime(timeRemaining.minutes) }}
              </p>
              <p class="text-sm lg:text-base text-white font-medium mt-2">
                Menit
              </p>
            </div>

            <div class="pt-2 text-xl md:text-2xl lg:text-3xl font-bold text-white">:</div>

            <!-- Seconds -->
            <div class="text-center">
              <p class="text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                {{ formatTime(timeRemaining.seconds) }}
              </p>
              <p class="text-sm lg:text-base text-white font-medium mt-2">
                Detik
              </p>
            </div>
          </div>

          <!-- Status Message -->
          <Button variant="outline" size="lg" 
            class="text-base dark:bg-foreground dark:text-background dark:border-foreground dark:hover:bg-foreground/30" as-child>
            <Link :href="auth.user ? `/vote/${kegiatan?.id}` : '/login'">
            Vote Sekarang!
            </Link>
          </Button>
        </div>

        <!-- Coming Soon State -->
        <div class="relative z-10 px-4 space-y-4 md:space-y-6 flex flex-col items-center justify-center text-shadow-md text-shadow-foreground/30" v-else>
          <h2 class="text-xl md:text-4xl font-semibold mb-4 text-white">Coming Soon</h2>
          <h2 class="text-2xl md:text-5xl font-semibold text-white">Musma FMIPA {{ dayjs().year() }}</h2>
          <p class="text-white font-medium text-sm md:text-base max-w-2xl text-center">
            Pemilihan Ketua Dewan Perwakilan Mahasiswa Fakultas Matematika dan Ilmu Pengetahuan Alam tahun {{
              dayjs().year() }} akan segera hadir! Tunggu dan pantau selalu informasinya.
          </p>
        </div>
      </div>

      <!-- FAQ Accordion -->
      <div class="w-full mt-4 md:max-w-7xl place-self-center px-4">
        <h1 class="text-lg md:text-xl lg:text-2xl mt-2 mb-6 font-bold text-center">Yang Sering Ditanyakan</h1>
        <Accordion type="single" class="w-full" collapsible :default-value="defaultValue">
          <AccordionItem v-for="item in accordionItems" :key="item.value" :value="item.value">
            <AccordionTrigger>{{ item.title }}</AccordionTrigger>
            <AccordionContent>
              {{ item.content }}
            </AccordionContent>
          </AccordionItem>
        </Accordion>
      </div>
    </div>
  </AppLayout>
</template>
