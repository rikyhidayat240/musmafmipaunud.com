<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { BreadcrumbItem, Kegiatan } from "@/types";
import { Head, Link, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";

const props = defineProps<{
    kegiatan?: Kegiatan;
}>();

const page = usePage();
const auth = computed(() => page.props.auth);
const title = auth.value.user ? "Beranda" : "Selamat Datang";

const breadcrumbs: BreadcrumbItem[] = [{ title: "Beranda", href: "/" }];

// Countdown
const currentTime = ref(new Date());
let interval: number | null = null;

const timeRemaining = computed(() => {
    const target = new Date(props.kegiatan?.waktu_mulai_raw ?? "");
    const now = currentTime.value;
    const diff = target.getTime() - now.getTime();
    if (diff <= 0) return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
    return {
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((diff % (1000 * 60)) / 1000),
        expired: false,
    };
});

const formatTime = (t: number) => t.toString().padStart(2, "0");

onMounted(() => { interval = setInterval(() => { currentTime.value = new Date(); }, 1000); });
onUnmounted(() => { if (interval) clearInterval(interval); });

const accordionItems = [
    { value: "item-1", title: "Apa saja syarat untuk mengikuti pemilihan?", content: `Anda harus terdaftar sebagai calon fungsionaris DPM FMIPA periode ${dayjs().year() + 1} dan mahasiswa aktif di fakultas dan program studi yang sesuai pada tahun ${dayjs().year()}.` },
    { value: "item-2", title: "Bagaimana cara melakukan registrasi akun?", content: "Anda cukup menyiapkan NIM dan nama lengkap yang sesuai dengan profil pada laman IMISSU. Kemudian, ikuti langkah-langkah yang terdapat pada laman registrasi akun." },
    { value: "item-3", title: "Bagaimana cara login ke dalam laman pemilihan?", content: "Anda dapat melakukan login dengan menggunakan email dan kata sandi yang telah Anda daftarkan sebelumnya." },
    { value: "item-4", title: "Bagaimana jika saya lupa kata sandi?", content: 'Anda dapat melakukan reset kata sandi melalui laman login dengan mengklik tautan "Lupa kata sandi?".' },
    { value: "item-5", title: "Kegiatan apa saja yang dapat saya ikuti?", content: `Anda dapat mengikuti kegiatan pemilihan calon ketua DPM FMIPA periode ${dayjs().year() + 1}.` },
    { value: "item-6", title: "Bagaimana tata cara melakukan pemilihan?", content: "Anda diharuskan untuk memiliki akun dan login terlebih dahulu untuk dapat melakukan pemilihan." },
    { value: "item-7", title: "Apakah saya dapat mengubah pilihan saya setelah memilih?", content: 'Tidak, setelah Anda mengklik tombol "Vote", pilihan Anda akan terkunci dan tidak dapat diubah.' },
    { value: "item-8", title: "Bagaimana cara memperbarui profil dan akun?", content: "Anda dapat memperbarui profil dan akun Anda melalui halaman pengaturan akun." },
];

const ctaLink = computed(() => auth.value.user ? `/vote/${props.kegiatan?.id}` : "/login");
</script>

<template>
    <Head :title="title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Page wrapper -->
        <div class="musma-bg min-h-full flex flex-col">

            <!-- Global Decorations -->
            <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
                <!-- Center Watermark (Foxes) -->
                <img src="/logo 2026.png" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] md:w-[600px] h-auto object-contain opacity-[0.04] dark:opacity-[0.06] select-none" alt="" />
                <!-- Corner Ornaments -->
                <img src="/image 62.png" class="absolute top-0 left-0 w-[120px] md:w-[220px] h-auto object-contain opacity-60 dark:opacity-40 rotate-180 -translate-x-1/4 -translate-y-1/4 select-none" alt="" />
                <img src="/image 63.png" class="absolute top-0 right-0 w-[90px] md:w-[180px] h-auto object-contain opacity-60 dark:opacity-40 rotate-180 translate-x-1/4 -translate-y-1/4 select-none" alt="" />
                <img src="/image 63.png" class="absolute bottom-0 left-0 w-[90px] md:w-[180px] h-auto object-contain opacity-60 dark:opacity-40 -translate-x-1/4 translate-y-1/4 select-none" alt="" />
                <img src="/image 62.png" class="absolute bottom-0 right-0 w-[120px] md:w-[220px] h-auto object-contain opacity-60 dark:opacity-40 translate-x-1/4 translate-y-1/4 select-none" alt="" />
            </div>

            <!-- ===== HERO SECTION ===== -->
            <section class="relative z-10 flex flex-col items-center text-center px-4 pt-12 pb-10 md:pt-16">

                <!-- Mandala Ornament (Inline) -->
                <img src="/image 61.png" class="w-20 md:w-28 h-auto object-contain mb-4 select-none" alt="Mandala" />

                <!-- Subtitle tag -->
                <p class="text-xs md:text-sm font-semibold tracking-[0.2em] uppercase mb-3" style="color:#9A6A72;">
                    Musyawarah Mahasiswa &bull; Lembaga Mahasiswa
                </p>

                <!-- Main title -->
                <h1 class="musma-section-title text-5xl md:text-7xl font-extrabold leading-tight mb-1">MUSMA</h1>
                <h2 class="text-3xl md:text-5xl font-bold mb-6" style="color:#DA8278; font-family:'Playfair Display',serif;">LM FMIPA {{ dayjs().year() }}</h2>

                <!-- Gold ornament divider -->
                <div class="flex items-center gap-3 mb-6 w-full max-w-sm">
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                    <span class="text-sm" style="color:#C5AA6C;">✦ ✦ ✦</span>
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                </div>

                <!-- Description text -->
                <p class="text-sm md:text-base max-w-lg mb-2 leading-relaxed" style="color:#5A2A34;">
                    Selamat datang di Bilik Suara Digital MUSMA LM FMIPA {{ dayjs().year() }}. Gunakan hak suaramu untuk memilih Ketua Dewan Perwakilan Mahasiswa FMIPA periode {{ dayjs().year() + 1 }}–{{ dayjs().year() + 3 }}.
                </p>
                <p class="text-xs md:text-sm italic mb-8" style="color:#9A6A72;">&ldquo;Satu Suara, Satu Perubahan Nyata.&rdquo;</p>

                <!-- Countdown (shown when kegiatan exists and not expired) -->
                <div v-if="kegiatan && !timeRemaining.expired" class="flex gap-4 md:gap-6 mb-8 items-start">
                    <div v-for="(val, label) in {Hari: timeRemaining.days, Jam: timeRemaining.hours, Menit: timeRemaining.minutes, Detik: timeRemaining.seconds}" :key="label" class="text-center">
                        <div class="musma-card w-16 h-16 md:w-20 md:h-20 flex items-center justify-center shadow-md">
                            <span class="text-2xl md:text-3xl font-bold" style="color:#51071B;">{{ formatTime(val) }}</span>
                        </div>
                        <p class="text-xs font-medium mt-1" style="color:#7A4A52;">{{ label }}</p>
                    </div>
                </div>

                <!-- CTA Button -->
                <Link v-if="kegiatan" :href="ctaLink" class="musma-btn text-base px-8 py-3.5 shadow-lg">
                    Masuk ke Bilik Suara
                </Link>
                <div v-else class="musma-card px-8 py-6 max-w-sm text-center shadow">
                    <p class="text-2xl font-bold musma-section-title mb-1">Coming Soon</p>
                    <p class="text-sm" style="color:#7A4A52;">Musma FMIPA {{ dayjs().year() }} akan segera hadir!</p>
                </div>

                <!-- Divider bottom -->
                <div class="flex items-center gap-3 mt-12 w-full max-w-sm">
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                    <span class="text-sm" style="color:#C5AA6C;">✦</span>
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                </div>
            </section>

            <!-- ===== FEATURE ICONS ===== -->
            <section class="relative z-10 px-4 pb-12">
                <div class="max-w-3xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background-color:rgba(81,7,27,0.08);">
                            <img src="/gembok.png" alt="Gembok" class="w-7 h-7 object-contain" />
                        </div>
                        <div>
                            <p class="font-bold text-sm" style="color:#51071B;">Rahasia &amp; Aman</p>
                            <p class="text-xs mt-1" style="color:#9A6A72;">Identitas dan pilihan Anda dijaga kerahasiaannya.</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background-color:rgba(81,7,27,0.08);">
                            <img src="/petir.png" alt="Petir" class="w-7 h-7 object-contain" />
                        </div>
                        <div>
                            <p class="font-bold text-sm" style="color:#51071B;">Login Untuk Memilih</p>
                            <p class="text-xs mt-1" style="color:#9A6A72;">Gunakan akun yang sudah di sajikan panitia.</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background-color:rgba(81,7,27,0.08);">
                            <img src="/gembok.png" alt="Satu Suara" class="w-7 h-7 object-contain" />
                        </div>
                        <div>
                            <p class="font-bold text-sm" style="color:#51071B;">Satu Orang, Satu Suara</p>
                            <p class="text-xs mt-1" style="color:#9A6A72;">Setiap pemilih hanya dapat memilih satu kali.</p>
                        </div>
                    </div>
                </div>

                <!-- bottom divider -->
                <div class="flex items-center gap-3 mt-10 max-w-md mx-auto">
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                    <span class="text-sm" style="color:#C5AA6C;">✦ ✦ ✦</span>
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                </div>
            </section>

            <!-- ===== FAQ ===== -->
            <section class="relative z-10 px-4 pb-16">
                <div class="max-w-3xl mx-auto">
                    <h2 class="musma-section-title text-xl md:text-2xl text-center mb-8">Yang Sering Ditanyakan</h2>
                    <Accordion type="single" class="w-full" collapsible default-value="item-1">
                        <AccordionItem
                            v-for="item in accordionItems"
                            :key="item.value"
                            :value="item.value"
                            class="border-b"
                            style="border-color:#E3D7D9;"
                        >
                            <AccordionTrigger class="text-sm font-semibold text-left" style="color:#51071B;">{{ item.title }}</AccordionTrigger>
                            <AccordionContent class="text-sm" style="color:#7A4A52;">{{ item.content }}</AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
            </section>

        </div>
    </AppLayout>
</template>
