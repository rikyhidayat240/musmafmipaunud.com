<script setup lang="ts">
import { Kandidat, Kegiatan } from "@/types";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";
import OrnamentDivider from "@/components/OrnamentDivider.vue";
import MaterialIcon from "@/components/MaterialIcon.vue";
import dayjs from "dayjs";

const props = defineProps<{ kegiatan: Kegiatan }>();

// 0=aturan, 1=kandidat, 2=vote/miih, 3=konfirmasi dialog, 4=terima kasih
const step = ref<0 | 1 | 2 | 4>(0);
const selectedKandidat = ref<Kandidat | null>(null);
const isProcessing = ref(false);
const showConfirm = ref(false);
const showStartConfirm = ref(false);
const expandedMisi = ref<Record<number, boolean>>({});

watch(step, () => window.scrollTo({ top: 0, behavior: "smooth" }));

function confirmStartVote() {
    showStartConfirm.value = false;
    step.value = 2;
}

function goToAturan() { step.value = 0; }
function goToKandidat() { step.value = 1; }

function selectKandidat(k: Kandidat) {
    selectedKandidat.value = k;
    showConfirm.value = true;
}

function cancelConfirm() {
    showConfirm.value = false;
    selectedKandidat.value = null;
}

function confirmVote() {
    if (!selectedKandidat.value) return;
    isProcessing.value = true;
    router.post("", { kandidat: selectedKandidat.value.id }, {
        onSuccess: () => { step.value = 4; showConfirm.value = false; },
        onFinish: () => { isProcessing.value = false; },
    });
}

function toggleMisi(id: number) {
    expandedMisi.value[id] = !expandedMisi.value[id];
}

const formatMisi = (misi: string): string[] => {
    if (!misi) return [];
    return misi.split(/\d+\.\s/).filter(item => item && item.trim()).map(item => item.trim());
};
</script>

<template>
    <Head title="Bilik Suara" />

    <AppLayout>
        <div class="musma-bg min-h-full flex flex-col relative transition-colors duration-300">

            <!-- Global Background Ornaments -->
            <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
                <!-- Center Watermark (Foxes) -->
                <img src="/logo 2026.png" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] md:w-[600px] h-auto object-contain opacity-[0.035] dark:opacity-[0.05] select-none" alt="" />
                <!-- Corner Balinese Ornaments -->
                <img src="/image 62.png" class="absolute top-0 left-0 w-[140px] md:w-[220px] h-auto object-contain opacity-25 dark:opacity-15 rotate-180 -translate-x-1/4 -translate-y-1/4 select-none" alt="" />
                <img src="/image 63.png" class="absolute top-0 right-0 w-[110px] md:w-[180px] h-auto object-contain opacity-25 dark:opacity-15 rotate-180 translate-x-1/4 -translate-y-1/4 select-none" alt="" />
                <img src="/image 63.png" class="absolute bottom-0 left-0 w-[110px] md:w-[180px] h-auto object-contain opacity-25 dark:opacity-15 -translate-x-1/4 translate-y-1/4 select-none" alt="" />
                <img src="/image 62.png" class="absolute bottom-0 right-0 w-[140px] md:w-[220px] h-auto object-contain opacity-25 dark:opacity-15 translate-x-1/4 translate-y-1/4 select-none" alt="" />
            </div>

            <!-- ============================
                 STEP 0: ATURAN (Node 252:1200)
                 ============================ -->
            <div v-if="step === 0" class="relative z-10 flex flex-col items-center px-4 pt-10 pb-16 max-w-3xl mx-auto w-full">

                <!-- Page tag -->
                <p class="text-xs font-bold tracking-[0.25em] uppercase text-[#9A6A72] dark:text-[#FCCE88]/80 mb-3 text-center">BILIK SUARA DIGITAL</p>

                <!-- Title -->
                <h1 class="font-serif text-2xl sm:text-3xl md:text-5xl font-bold text-[#3B0613] dark:text-[#FAF4EB] text-center mb-5 tracking-tight px-2">
                    {{ kegiatan?.nama || 'Pemilihan Ketua DPM FMIPA ' + (dayjs().year() + 1) }}
                </h1>

                <!-- Large Flourish Divider -->
                <OrnamentDivider variant="large" class="mb-6 sm:mb-8" />

                <!-- Syarat & Ketentuan card -->
                <div class="musma-card w-full max-w-2xl px-5 sm:px-8 py-5 sm:py-7 mb-6 border border-[#3B0613]/25 dark:border-[#FCCE88]/20 rounded-2xl shadow-sm bg-[#FFFDFB] dark:bg-[#27060F]">
                    <div class="flex items-center gap-3 mb-4 sm:mb-5">
                        <div class="w-1.5 h-6 rounded-full bg-[#3B0613] dark:bg-[#FCCE88]"></div>
                        <h2 class="font-bold text-base sm:text-lg font-serif text-[#3B0613] dark:text-[#FAF4EB]">Syarat &amp; Ketentuan</h2>
                    </div>
                    <div class="space-y-3 sm:space-y-3.5 text-xs sm:text-sm leading-relaxed text-[#5A2A34] dark:text-[#F3DFD1]">
                        <p>Pemilih adalah mahasiswa aktif program studi sarjana Fakultas MIPA.</p>
                        <p>Setiap akun hanya dapat melakukan pemilihan sebanyak satu kali.</p>
                        <p>Gunakan hak pilih secara mandiri dengan berlandaskan Luberjurdil.</p>
                        <p>Pilihan yang telah dikirim bersifat final dan tidak dapat diubah.</p>
                        <p>Setiap pelanggaran akan ditindak sesuai ketentuan yang berlaku.</p>
                    </div>
                </div>

                <!-- Small Flourish Divider -->
                <OrnamentDivider variant="small" class="mb-6 sm:mb-8" />

                <!-- Event card (Kotak Info Pemilihan) -->
                <div class="w-full max-w-md rounded-2xl overflow-hidden shadow-xl border border-[#3B0613]/15 dark:border-[#FCCE88]/20 mb-8 sm:mb-10 transition-transform duration-300 hover:scale-[1.01]">
                    <!-- Top warm cream header with logo -->
                    <div class="bg-[#FAF7F2] dark:bg-[#1A0308] py-5 sm:py-6 px-4 sm:px-6 flex flex-col items-center justify-center border-b border-[#3B0613]/10 dark:border-[#FCCE88]/15">
                        <img src="/logo 2026.png" class="h-12 sm:h-14 w-auto object-contain mb-2 select-none" alt="Musma Logo" />
                        <p class="text-[10px] sm:text-[11px] font-bold tracking-[0.2em] text-[#3B0613] dark:text-[#FAF4EB] uppercase">MUSMA LM FMIPA &bull; {{ dayjs().year() }}</p>
                    </div>
                    <!-- Bottom deep wine section -->
                    <div class="bg-[#3B0613] dark:bg-[#20040C] p-5 sm:p-6 text-center text-white">
                        <h3 class="font-serif font-bold text-lg sm:text-xl mb-1 text-white">{{ kegiatan?.nama || 'Pemilihan Ketua DPM FMIPA' }}</h3>
                        <p class="text-xs font-semibold text-[#F5C249] mb-4 sm:mb-5 tracking-wide">Sedang berlangsung</p>
                        <button @click="goToKandidat" class="inline-block text-xs font-bold px-5 sm:px-6 py-2 rounded-full border border-white/40 text-white hover:bg-white/10 transition-colors">
                            Lihat Kandidat
                        </button>
                    </div>
                </div>

                <!-- Action CTA buttons -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 sm:gap-4 w-full max-w-md">
                    <Link href="/" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-sm bg-[#3B0613] text-white hover:bg-[#51071B] dark:bg-[#27060F] dark:border dark:border-[#FCCE88]/40 dark:text-[#FAF4EB] dark:hover:bg-[#340713] transition-colors flex items-center justify-center gap-2 shadow-md">
                        <MaterialIcon icon="arrow_back" size="sm" />
                        Kembali ke Beranda
                    </Link>
                    <button @click="showStartConfirm = true" class="w-full sm:w-auto px-7 py-3 rounded-xl font-bold text-sm text-[#3B0613] musma-btn-gold transition-transform hover:scale-[1.02] flex items-center justify-center gap-2 shadow-lg">
                        Mulai Pemilihan
                        <MaterialIcon icon="arrow_forward" size="sm" />
                    </button>
                </div>
            </div>

            <!-- ============================
                 STEP 1: KANDIDAT LIST (Node 252:1334)
                 ============================ -->
            <div v-if="step === 1" class="relative z-10 flex flex-col items-center px-4 pt-10 pb-16 max-w-4xl mx-auto w-full">

                <!-- Page tag -->
                <p class="text-xs font-bold tracking-[0.25em] uppercase text-[#9A6A72] dark:text-[#FCCE88]/80 mb-3 text-center">BILIK SUARA DIGITAL</p>

                <!-- Title -->
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-[#3B0613] dark:text-[#FAF4EB] text-center mb-5 tracking-tight">
                    Kandidat {{ kegiatan?.nama || 'Ketua DPM FMIPA ' + (dayjs().year() + 1) }}
                </h1>

                <!-- Large Flourish Divider -->
                <OrnamentDivider variant="large" class="mb-6" />

                <!-- Subheader navigation -->
                <div class="w-full flex items-center gap-3 text-sm mb-6 text-[#9A6A72] dark:text-[#FCCE88]/70">
                    <button @click="goToAturan" class="font-semibold text-[#3B0613] dark:text-[#FAF4EB] hover:underline flex items-center gap-1 transition-colors">
                        <MaterialIcon icon="arrow_back" size="sm" /> Kembali
                    </button>
                    <span class="text-[#3B0613]/30 dark:text-[#FCCE88]/30">|</span>
                    <span class="font-medium text-[#5A2A34] dark:text-[#F3DFD1]">Kandidat {{ kegiatan?.nama || 'Pemilihan Ketua DPM FMIPA' }}</span>
                </div>

                <!-- Candidate cards -->
                <div class="w-full space-y-8">
                    <div v-for="kandidat in kegiatan?.kandidat" :key="kandidat.id"
                         class="w-full bg-[#FFFDFB] dark:bg-[#27060F] rounded-3xl border border-[#3B0613]/30 dark:border-[#FCCE88]/25 shadow-md overflow-hidden transition-shadow hover:shadow-lg">
                        <div class="flex flex-col md:flex-row">
                            <!-- Left column (Portrait + Number + Badge) -->
                            <div class="md:w-72 flex-shrink-0 flex flex-col items-center justify-center p-6 sm:p-8 bg-[#FAF7F2] dark:bg-[#1A0308] md:border-r border-b md:border-b-0 border-[#3B0613]/20 dark:border-[#FCCE88]/20">
                                <div class="relative mb-3 sm:mb-4">
                                    <!-- Circular photo with dark maroon border -->
                                    <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-full overflow-hidden border-2 border-[#3B0613] dark:border-[#FCCE88] shadow-md bg-white">
                                        <img
                                            :src="kandidat.foto ? `/storage/${kandidat.foto}` : '/assets/blank-profile-picture.webp'"
                                            alt="Foto Kandidat"
                                            class="w-full h-full object-cover object-top"
                                        />
                                    </div>
                                    <!-- No. 01 badge -->
                                    <div class="absolute -top-1 -right-2 px-2.5 sm:px-3 py-0.5 rounded-full bg-white dark:bg-[#27060F] border border-[#3B0613] dark:border-[#FCCE88] text-[#3B0613] dark:text-[#FCCE88] font-bold text-xs shadow-sm">
                                        No. {{ String(kandidat.no_urut).padStart(2, '0') }}
                                    </div>
                                </div>
                                <!-- Affiliation badge -->
                                <span class="px-4 sm:px-5 py-1 rounded-full text-xs font-bold text-[#3B0613] bg-[#F5C249] shadow-sm uppercase tracking-wider text-center max-w-full">
                                    {{ kandidat.mahasiswa?.[0]?.program_studi?.nama || 'FMIPA' }}
                                </span>
                            </div>

                            <!-- Right column (Name, Role, Visi, Misi) -->
                            <div class="flex-1 p-5 sm:p-8 flex flex-col">
                                <!-- Candidate Name -->
                                <h2 class="font-serif font-bold text-xl sm:text-2xl text-[#3B0613] dark:text-[#FAF4EB] mb-2">
                                    <span v-for="(mhs, i) in kandidat.mahasiswa" :key="mhs.nim">
                                        {{ mhs.nama }}<span v-if="i < kandidat.mahasiswa!.length - 1"> &amp; </span>
                                    </span>
                                </h2>

                                <!-- Role & Affiliation pill -->
                                <div class="flex flex-wrap gap-2 mb-4 sm:mb-5">
                                    <span v-for="mhs in kandidat.mahasiswa" :key="mhs.nim"
                                        class="inline-flex flex-wrap items-center gap-1.5 px-3 py-1 rounded-2xl text-xs font-semibold border border-[#3B0613]/30 dark:border-[#FCCE88]/30 text-[#3B0613] dark:text-[#FAF4EB] bg-[#FAF7F2] dark:bg-[#1A0308] max-w-full text-wrap break-words leading-relaxed">
                                        <MaterialIcon icon="person" size="xs" class="text-[#3B0613] dark:text-[#FCCE88]" />
                                        {{ mhs.program_studi?.nama || 'FMIPA' }}'{{ mhs.angkatan ? String(mhs.angkatan).slice(-2) : '' }} &mdash; Calon {{ mhs.pivot?.jabatan?.charAt(0).toUpperCase() + mhs.pivot?.jabatan?.slice(1) }}
                                    </span>
                                </div>

                                <!-- Horizontal line -->
                                <div class="border-b border-[#3B0613]/15 dark:border-[#FCCE88]/15 mb-5"></div>

                                <!-- Visi -->
                                <div class="mb-5">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <MaterialIcon icon="visibility" size="xs" class="text-[#3B0613] dark:text-[#FCCE88]" />
                                        <h3 class="text-sm font-bold font-serif text-[#3B0613] dark:text-[#FAF4EB]">Visi</h3>
                                    </div>
                                    <p class="text-sm leading-relaxed text-[#5A2A34] dark:text-[#F3DFD1] pl-5">{{ kandidat.visi }}</p>
                                </div>

                                <!-- Misi -->
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <MaterialIcon icon="flag" size="xs" class="text-[#3B0613] dark:text-[#FCCE88]" />
                                        <h3 class="text-sm font-bold font-serif text-[#3B0613] dark:text-[#FAF4EB]">Misi</h3>
                                    </div>
                                    <ul class="space-y-2.5 pl-5">
                                        <li v-for="(m, i) in formatMisi(kandidat.misi)" :key="i" class="flex items-start gap-3 text-sm text-[#5A2A34] dark:text-[#F3DFD1]">
                                            <span class="w-4 h-4 rounded-full bg-[#3B0613] dark:bg-[#FCCE88] text-[#FAF7F2] dark:text-[#27060F] text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                                                {{ i + 1 }}
                                            </span>
                                            <span class="leading-relaxed">{{ m }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom action button -->
                <div class="mt-8 flex justify-center">
                    <button @click="showStartConfirm = true" class="px-8 py-3 rounded-xl font-bold text-sm text-[#3B0613] musma-btn-gold transition-transform hover:scale-[1.02] flex items-center gap-2 shadow-lg">
                        Lanjut ke Bilik Suara
                        <MaterialIcon icon="arrow_forward" size="sm" />
                    </button>
                </div>
            </div>

            <!-- ============================
                 STEP 2: VOTE / MIIH (Node 252:1743)
                 ============================ -->
            <div v-if="step === 2" class="relative z-10 flex flex-col items-center px-4 pt-10 pb-16 max-w-4xl mx-auto w-full">

                <!-- Page tag -->
                <p class="text-xs font-bold tracking-[0.25em] uppercase text-[#9A6A72] dark:text-[#FCCE88]/80 mb-3 text-center">BILIK SUARA DIGITAL</p>

                <!-- Title -->
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-[#3B0613] dark:text-[#FAF4EB] text-center mb-5 tracking-tight">
                    {{ kegiatan?.nama || 'Pemilihan Ketua DPM FMIPA ' + (dayjs().year() + 1) }}
                </h1>

                <!-- Large Flourish Divider -->
                <OrnamentDivider variant="large" class="mb-8" />

                <!-- Voting Ballot Cards (2 columns on desktop) -->
                <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">
                    <div v-for="kandidat in kegiatan?.kandidat" :key="kandidat.id"
                         class="rounded-3xl overflow-hidden shadow-xl border border-[#3B0613]/15 dark:border-[#FCCE88]/25 flex flex-col bg-[#FFFDFB] dark:bg-[#27060F]">

                        <!-- Top banner with maroon gradient and portrait -->
                        <div class="relative pt-5 sm:pt-6 pb-0 px-4 sm:px-6 flex flex-col justify-between overflow-hidden"
                             style="background: linear-gradient(135deg, #7D2D3D 0%, #3B0613 100%); min-height: 200px;">
                            <!-- Subtle corner ornament watermark -->
                            <img src="/image 63.png" class="absolute -top-4 -right-4 w-20 sm:w-24 h-auto opacity-20 rotate-180 pointer-events-none select-none" alt="" />

                            <!-- Candidate info overlay top left -->
                            <div class="relative z-10 text-left">
                                <h3 class="font-serif font-bold text-white text-sm sm:text-base leading-tight">
                                    <span v-for="(mhs, i) in kandidat.mahasiswa" :key="mhs.nim">
                                        {{ mhs.nama }}<span v-if="i < kandidat.mahasiswa!.length - 1"> &amp; </span>
                                    </span>
                                </h3>
                                <p class="text-[11px] sm:text-xs text-white/80 font-medium mt-0.5">
                                    {{ kandidat.mahasiswa?.[0]?.program_studi?.nama || 'FMIPA' }} {{ kandidat.mahasiswa?.[0]?.angkatan ? String(kandidat.mahasiswa?.[0]?.angkatan).slice(-2) : '' }}
                                </p>
                            </div>

                            <!-- Portrait in center/bottom -->
                            <div class="relative z-10 flex justify-center items-end mt-2">
                                <div class="w-32 sm:w-36 h-36 sm:h-44 overflow-hidden rounded-t-2xl">
                                    <img
                                        :src="kandidat.foto ? `/storage/${kandidat.foto}` : '/assets/blank-profile-picture.webp'"
                                        alt="Foto Kandidat"
                                        class="w-full h-full object-cover object-top"
                                    />
                                </div>
                            </div>

                            <!-- Overlapping number badge -->
                            <div class="absolute bottom-2 sm:bottom-3 left-3 sm:left-4 z-20 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-[#3B0613] dark:bg-[#1A0308] border-2 border-[#FFFDFB] dark:border-[#FCCE88] text-white dark:text-[#FCCE88] flex items-center justify-center font-black text-lg sm:text-xl shadow-lg">
                                {{ kandidat.no_urut }}
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="p-5 sm:p-6 flex flex-col flex-1 text-left">
                            <!-- Candidate Name -->
                            <h2 class="font-serif font-bold text-lg text-[#3B0613] dark:text-[#FAF4EB] mb-0.5">
                                <span v-for="(mhs, i) in kandidat.mahasiswa" :key="mhs.nim">
                                    {{ mhs.nama }}<span v-if="i < kandidat.mahasiswa!.length - 1"> &amp; </span>
                                </span>
                            </h2>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#DA8278] dark:text-[#FCCE88]/90 mb-4">
                                CALON KETUA DPM FMIPA {{ dayjs().year() + 1 }}
                            </p>

                            <!-- VISI -->
                            <div class="mb-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-[#DA8278] dark:text-[#FCCE88]/90 mb-1">VISI</p>
                                <p class="text-sm leading-relaxed text-[#5A2A34] dark:text-[#F3DFD1]">{{ kandidat.visi }}</p>
                            </div>

                            <!-- MISI Collapsible Accordion -->
                            <div class="mb-4">
                                <button
                                    @click="toggleMisi(kandidat.id)"
                                    class="text-xs font-bold text-[#3B0613] dark:text-[#FCCE88] hover:underline flex items-center gap-1.5 mb-2.5 transition-colors"
                                >
                                    <MaterialIcon :icon="expandedMisi[kandidat.id] ? 'expand_less' : 'expand_more'" size="xs" />
                                    <span>{{ expandedMisi[kandidat.id] ? 'Sembunyikan Misi' : 'Lihat Misi' }}</span>
                                </button>
                                <div v-if="expandedMisi[kandidat.id]" class="space-y-2 pt-1 transition-all">
                                    <div v-for="(m, i) in formatMisi(kandidat.misi)" :key="i" class="flex items-start gap-2.5 text-xs text-[#5A2A34] dark:text-[#F3DFD1]">
                                        <span class="w-4 h-4 rounded-full bg-[#3B0613] dark:bg-[#FCCE88] text-white dark:text-[#27060F] font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">
                                            {{ i + 1 }}
                                        </span>
                                        <span class="leading-relaxed">{{ m }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Flourish Divider -->
                            <div class="my-4">
                                <OrnamentDivider variant="small" />
                            </div>

                            <!-- PILIH button -->
                            <button
                                @click="selectKandidat(kandidat)"
                                class="w-full mt-auto py-3 px-6 rounded-xl font-bold text-sm tracking-wider uppercase bg-[#3B0613] hover:bg-[#51071B] dark:bg-[#FCCE88] dark:text-[#27060F] dark:hover:bg-[#FBE8B5] text-white shadow-md hover:shadow-lg transition-all text-center"
                            >
                                PILIH
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================
                 STEP 4: TERIMA KASIH (Node 254:2)
                 ============================ -->
            <div v-if="step === 4" class="relative z-10 flex flex-col items-center justify-center px-4 pt-12 pb-16 text-center max-w-xl mx-auto w-full min-h-[65vh]">

                <!-- Mandala graphic -->
                <img src="/image 61.png" class="w-32 md:w-36 h-auto object-contain mb-4 select-none opacity-90 dark:opacity-80" alt="Mandala" />

                <!-- Large Flourish Divider -->
                <OrnamentDivider variant="large" class="mb-4" />

                <!-- Title -->
                <h1 class="font-serif font-bold text-4xl md:text-5xl text-[#3B0613] dark:text-[#FAF4EB] mb-2 tracking-tight">Terima Kasih!</h1>

                <!-- Subtitle in salmon/rose -->
                <p class="text-base md:text-lg font-semibold text-[#DA8278] dark:text-[#FCCE88] mb-4">Suara Anda Telah Direkam</p>

                <!-- Description -->
                <p class="text-sm leading-relaxed text-[#5A2A34] dark:text-[#F3DFD1] max-w-md mb-8">
                    Terima kasih telah berpartisipasi dalam MUSMA FMIPA {{ dayjs().year() }}. Suara Anda adalah kontribusi nyata untuk masa depan DPM FMIPA yang lebih baik.
                </p>

                <!-- Return button -->
                <Link href="/" class="px-8 py-3 rounded-xl font-bold text-sm bg-[#3B0613] hover:bg-[#51071B] dark:bg-[#FCCE88] dark:text-[#27060F] dark:hover:bg-[#FBE8B5] text-white shadow-lg transition-transform hover:scale-[1.02] mb-10 inline-flex items-center gap-2">
                    <MaterialIcon icon="arrow_back" size="sm" />
                    Kembali ke Beranda
                </Link>

                <!-- Bottom session lock banner -->
                <div class="w-full pt-6 border-t border-[#3B0613]/15 dark:border-[#FCCE88]/20 text-center">
                    <p class="text-sm font-medium text-[#9A6A72] dark:text-[#FCCE88]/80 flex items-center justify-center gap-2 mb-1">
                        <MaterialIcon icon="lock" size="sm" class="text-[#9A6A72] dark:text-[#FCCE88]/80" />
                        Sesi pemilihan Anda telah berakhir.
                    </p>
                    <p class="text-xs text-[#9A6A72] dark:text-[#FCCE88]/60 mb-5">Anda tidak dapat melakukan pemilihan ulang.</p>
                    <div class="flex items-center justify-center gap-2">
                        <img src="/logo 2026.png" class="h-6 w-auto object-contain opacity-80" alt="Logo" />
                        <span class="text-xs font-bold text-[#3B0613] dark:text-[#FAF4EB] tracking-wider uppercase">MUSMA LM FMIPA {{ dayjs().year() }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================
             KONFIRMASI DIALOG (Node 252:2084)
             ============================ -->
        <Teleport to="body">
            <div v-if="showConfirm" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                <!-- Blurred backdrop -->
                <div class="absolute inset-0 bg-[#190308]/75 backdrop-blur-sm" @click="cancelConfirm"></div>

                <!-- Modal dialog -->
                <div class="relative bg-[#FFFDFB] dark:bg-[#27060F] rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl border border-[#3B0613]/15 dark:border-[#FCCE88]/25 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                    <!-- Mandala watermark in top right -->
                    <img src="/image 61.png" class="absolute -top-8 -right-8 w-32 h-auto opacity-15 dark:opacity-10 pointer-events-none select-none" alt="" />

                    <!-- Candidate number badge -->
                    <div class="w-14 h-14 rounded-full bg-[#3B0613] dark:bg-[#FCCE88] text-white dark:text-[#27060F] font-black text-2xl flex items-center justify-center mx-auto mb-4 shadow-md">
                        {{ selectedKandidat?.no_urut }}
                    </div>

                    <!-- Title & Details -->
                    <h2 class="font-serif font-bold text-xl text-[#3B0613] dark:text-[#FAF4EB] mb-2">Konfirmasi Pilihan</h2>
                    <p class="text-xs text-[#5A2A34] dark:text-[#F3DFD1] mb-1">Apakah Anda yakin ingin memilih</p>
                    <p class="font-serif font-bold text-lg text-[#3B0613] dark:text-[#FCCE88] mb-1">
                        <span v-for="(mhs, i) in selectedKandidat?.mahasiswa" :key="mhs.nim">
                            {{ mhs.nama }}<span v-if="i < (selectedKandidat?.mahasiswa?.length ?? 0) - 1"> &amp; </span>
                        </span>
                    </p>
                    <p class="text-xs text-[#5A2A34] dark:text-[#F3DFD1] mb-4">sebagai Ketua DPM FMIPA?</p>

                    <!-- Warning box with salmon/rose tint -->
                    <div class="rounded-xl p-3 text-xs text-left text-[#5A2A34] dark:text-[#FAF4EB] flex items-start gap-2.5 mb-6 bg-[rgba(218,130,120,0.2)] dark:bg-[#3D0A17] dark:border dark:border-[#FCCE88]/20">
                        <MaterialIcon icon="warning" size="sm" class="flex-shrink-0 text-[#3B0613] dark:text-[#FCCE88]" />
                        <span>Tindakan ini <strong>tidak dapat diubah</strong> setelah dikonfirmasi.</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <button
                            @click="cancelConfirm"
                            :disabled="isProcessing"
                            class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm border-2 border-[#3B0613] dark:border-[#FCCE88]/50 text-[#3B0613] dark:text-[#FAF4EB] hover:bg-[#3B0613]/5 dark:hover:bg-[#FCCE88]/10 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            @click="confirmVote"
                            :disabled="isProcessing"
                            class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm bg-[#3B0613] hover:bg-[#51071B] dark:bg-[#FCCE88] dark:text-[#27060F] dark:hover:bg-[#FBE8B5] text-white shadow-md transition-colors flex items-center justify-center gap-1.5"
                        >
                            <MaterialIcon v-if="isProcessing" icon="progress_activity" size="sm" class="animate-spin" />
                            <span v-else>Ya, Yakin</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ============================
             KONFIRMASI MULAI PEMILIHAN DIALOG
             ============================ -->
        <Teleport to="body">
            <div v-if="showStartConfirm" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                <!-- Blurred backdrop -->
                <div class="absolute inset-0 bg-[#190308]/75 backdrop-blur-sm" @click="showStartConfirm = false"></div>

                <!-- Modal dialog -->
                <div class="relative bg-[#FFFDFB] dark:bg-[#27060F] rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl border border-[#3B0613]/15 dark:border-[#FCCE88]/25 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                    <!-- Warning icon circle -->
                    <div class="w-12 h-12 rounded-full bg-[#3B0613]/10 dark:bg-[#FCCE88]/15 text-[#3B0613] dark:text-[#FCCE88] flex items-center justify-center mx-auto mb-4">
                        <MaterialIcon icon="warning" size="md" />
                    </div>

                    <h2 class="font-serif font-bold text-xl text-[#3B0613] dark:text-[#FAF4EB] mb-2">Perhatian</h2>
                    <p class="text-xs text-[#5A2A34] dark:text-[#F3DFD1] leading-relaxed mb-6">
                        Apakah Anda telah membaca, memahami, dan menyetujui seluruh Syarat &amp; Ketentuan pemilihan ini?
                    </p>

                    <div class="flex items-center gap-3">
                        <button
                            @click="showStartConfirm = false"
                            class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm border-2 border-[#3B0613] dark:border-[#FCCE88]/50 text-[#3B0613] dark:text-[#FAF4EB] hover:bg-[#3B0613]/5 dark:hover:bg-[#FCCE88]/10 transition-colors"
                        >
                            Belum
                        </button>
                        <button
                            @click="confirmStartVote"
                            class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm bg-[#3B0613] hover:bg-[#51071B] dark:bg-[#FCCE88] dark:text-[#27060F] dark:hover:bg-[#FBE8B5] text-white shadow-md transition-colors"
                        >
                            Ya, Setuju
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>
