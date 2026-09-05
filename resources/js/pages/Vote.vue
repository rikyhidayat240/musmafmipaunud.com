<script setup lang="ts">
import { Kandidat, Kegiatan } from "@/types";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { LoaderCircle, ChevronLeft, ChevronRight, Eye, EyeOff, TriangleAlert } from "lucide-vue-next";
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
    return misi.split(/\d+\.\s/).filter(item => item.trim()).map(item => item.trim());
};
</script>

<template>
    <Head title="Bilik Suara" />

    <AppLayout>
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

            <!-- ============================
                 STEP 0: ATURAN
                 ============================ -->
            <div v-if="step === 0" class="relative z-10 flex flex-col items-center px-4 pt-10 pb-16 max-w-3xl mx-auto w-full">

                <!-- Mandala Ornament (Inline) -->
                <img src="/image 61.png" class="w-20 md:w-24 h-auto object-contain mb-3 select-none" alt="Mandala" />

                <!-- Page tag -->
                <p class="text-xs font-semibold tracking-[0.2em] uppercase mb-4" style="color:#9A6A72;">Bilik Suara Digital</p>

                <!-- Title -->
                <h1 class="musma-section-title text-2xl md:text-4xl text-center mb-4">
                    Pemilihan Ketua DPM FMIPA {{ dayjs().year() + 1 }}
                </h1>

                <!-- Gold divider -->
                <div class="flex items-center gap-3 mb-8 w-full max-w-xs">
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                    <span style="color:#C5AA6C;">✦</span>
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                </div>

                <!-- Syarat card -->
                <div class="musma-card w-full px-6 py-5 mb-6 shadow">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-1 h-5 rounded-full" style="background-color:#51071B;"></div>
                        <h2 class="font-bold text-base" style="color:#51071B;">Syarat &amp; Ketentuan</h2>
                    </div>
                    <ol class="space-y-2.5 text-sm list-decimal list-inside" style="color:#5A2A34;">
                        <li>Mahasiswa/i yang dapat memilih adalah mahasiswa/i aktif program studi sarjana Fakultas MIPA.</li>
                        <li>Mahasiswa/i hanya bisa melakukan pemilihan sebanyak satu kali tanpa adanya pengulangan.</li>
                        <li>Mahasiswa/i diharapkan menggunakan hak pilihnya dan memilih dengan berlandaskan Luberjurdil.</li>
                        <li>Hasil pemilihan bersifat mutlak dan tidak dapat diganggu gugat, sesuai dengan aturan yang telah ditetapkan.</li>
                        <li>Setiap pelanggaran terhadap syarat dan ketentuan ini akan dikenakan sanksi sesuai dengan peraturan yang berlaku.</li>
                    </ol>
                </div>

                <!-- Gold divider -->
                <div class="flex items-center gap-3 mb-6 w-full max-w-xs">
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                    <span style="color:#C5AA6C;">✦</span>
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                </div>

                <!-- Election info card -->
                <div class="musma-election-card w-full px-6 py-5 mb-8 shadow-lg">
                    <p class="text-xs font-semibold tracking-widest uppercase mb-2" style="color:rgba(235,206,103,0.7);">
                        Musyawarah Mahasiswa &bull; FMIPA {{ dayjs().year() }}
                    </p>
                    <h3 class="text-lg font-bold text-white mb-1">{{ kegiatan.nama }}</h3>
                    <span class="musma-badge-ongoing text-xs mb-4 inline-flex">Sedang berlangsung</span>
                    <div class="mt-3">
                        <button @click="goToKandidat" class="text-xs font-semibold px-4 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10 transition-colors">
                            Lihat Kandidat
                        </button>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 w-full justify-center">
                    <Link href="/" class="musma-btn-outline flex items-center gap-2 justify-center">
                        <ChevronLeft class="w-4 h-4" />
                        Kembali ke Beranda
                    </Link>
                    <button @click="showStartConfirm = true" class="musma-btn flex items-center gap-2 justify-center">
                        Mulai Pemilihan
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- ============================
                 STEP 1: KANDIDAT LIST
                 ============================ -->
            <div v-if="step === 1" class="relative z-10 flex flex-col items-center px-4 pt-10 pb-16 max-w-4xl mx-auto w-full">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-xs mb-6 self-start" style="color:#9A6A72;">
                    <button @click="goToAturan" class="hover:text-[#51071B] transition-colors flex items-center gap-1">
                        <ChevronLeft class="w-3.5 h-3.5" /> Kembali
                    </button>
                    <span>/</span>
                    <span style="color:#51071B;">Kandidat Pemilihan Ketua DPM FMIPA</span>
                </div>

                <!-- Mandala Ornament (Inline) -->
                <img src="/image 61.png" class="w-20 md:w-24 h-auto object-contain mb-3 select-none" alt="Mandala" />

                <p class="text-xs font-semibold tracking-[0.2em] uppercase mb-3" style="color:#9A6A72;">Bilik Suara Digital</p>
                <h1 class="musma-section-title text-2xl md:text-3xl text-center mb-4">Kandidat Ketua DPM FMIPA {{ dayjs().year() + 1 }}</h1>

                <div class="flex items-center gap-3 mb-8 w-full max-w-xs">
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                    <span style="color:#C5AA6C;">✦</span>
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                </div>

                <!-- Candidate cards -->
                <div class="w-full space-y-6">
                    <div v-for="kandidat in kegiatan.kandidat" :key="kandidat.id" class="musma-card overflow-hidden shadow-md">
                        <div class="flex flex-col sm:flex-row">
                            <!-- Photo -->
                            <div class="sm:w-44 flex-shrink-0 flex items-center justify-center p-6" style="background-color:#F5ECEE;">
                                <div class="relative">
                                    <div class="w-28 h-28 rounded-full overflow-hidden border-2 shadow" style="border-color:#E3D7D9;">
                                        <img
                                            :src="kandidat.foto ? `/storage/${kandidat.foto}` : '/assets/blank-profile-picture.webp'"
                                            alt="Foto"
                                            class="w-full h-full object-cover object-top"
                                        />
                                    </div>
                                    <div class="musma-num-badge absolute -top-1 -right-1 !w-8 !h-8 !text-sm shadow">{{ kandidat.no_urut }}</div>
                                </div>
                            </div>
                            <!-- Info -->
                            <div class="flex-1 p-6">
                                <h2 class="musma-section-title text-lg mb-1">
                                    <span v-for="(mhs, i) in kandidat.mahasiswa" :key="mhs.nim">
                                        {{ mhs.nama }}<span v-if="i < kandidat.mahasiswa!.length - 1"> &amp; </span>
                                    </span>
                                </h2>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span v-for="mhs in kandidat.mahasiswa" :key="mhs.nim"
                                        class="text-xs px-2.5 py-0.5 rounded-full font-medium"
                                        style="background-color:rgba(81,7,27,0.08); color:#51071B;">
                                        {{ mhs.nim }} &mdash; Calon {{ mhs.pivot?.jabatan?.charAt(0).toUpperCase() + mhs.pivot?.jabatan?.slice(1) }}
                                    </span>
                                </div>
                                <div class="border-t mb-4" style="border-color:#E3D7D9;"></div>
                                <!-- Visi -->
                                <div class="mb-3">
                                    <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#9A6A72;">Visi</p>
                                    <p class="text-sm leading-relaxed" style="color:#5A2A34;">{{ kandidat.visi }}</p>
                                </div>
                                <!-- Misi -->
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider mb-2" style="color:#9A6A72;">Misi</p>
                                    <ul class="space-y-1.5">
                                        <li v-for="(m, i) in formatMisi(kandidat.misi)" :key="i" class="flex items-start gap-2 text-sm" style="color:#5A2A34;">
                                            <span class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold mt-0.5" style="background-color:#51071B; color:#F5EFE6;">{{ i + 1 }}</span>
                                            {{ m }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================
                 STEP 2: VOTE / MIIH
                 ============================ -->
            <div v-if="step === 2" class="relative z-10 flex flex-col items-center px-4 pt-10 pb-16 max-w-5xl mx-auto w-full">

                <!-- Mandala Ornament (Inline) -->
                <img src="/image 61.png" class="w-20 md:w-24 h-auto object-contain mb-3 select-none" alt="Mandala" />

                <p class="text-xs font-semibold tracking-[0.2em] uppercase mb-3" style="color:#9A6A72;">Bilik Suara Digital</p>
                <h1 class="musma-section-title text-2xl md:text-3xl text-center mb-4">
                    Pemilihan Ketua DPM FMIPA {{ dayjs().year() + 1 }}
                </h1>
                <div class="flex items-center gap-3 mb-8 w-full max-w-xs">
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                    <span style="color:#C5AA6C;">✦</span>
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                </div>

                <!-- Voting candidate cards - side by side on desktop -->
                <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="kandidat in kegiatan.kandidat" :key="kandidat.id" class="musma-card overflow-hidden shadow-md flex flex-col">
                        <!-- Photo header -->
                        <div class="relative flex justify-center items-end pt-8 pb-0" style="background-color:#F5ECEE; min-height:200px;">
                            <!-- Number badge -->
                            <div class="musma-num-badge absolute top-4 left-4 !w-9 !h-9 shadow-md text-base">{{ kandidat.no_urut }}</div>
                            <!-- Photo circle -->
                            <div class="w-36 h-44 rounded-t-full overflow-hidden shadow-inner border-2" style="border-color:#E3D7D9;">
                                <img
                                    :src="kandidat.foto ? `/storage/${kandidat.foto}` : '/assets/blank-profile-picture.webp'"
                                    alt="Foto"
                                    class="w-full h-full object-cover object-top"
                                />
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="flex flex-col flex-1 p-5">
                            <h2 class="musma-section-title text-base mb-0.5">
                                <span v-for="(mhs, i) in kandidat.mahasiswa" :key="mhs.nim">
                                    {{ mhs.nama }}<span v-if="i < kandidat.mahasiswa!.length - 1"> &amp; </span>
                                </span>
                            </h2>
                            <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color:#9A6A72;">
                                Calon Ketua DPM FMIPA {{ dayjs().year() + 1 }}
                            </p>

                            <!-- Visi -->
                            <div class="mb-3">
                                <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:#9A6A72;">VISI</p>
                                <p class="text-sm leading-relaxed" style="color:#5A2A34;">{{ kandidat.visi }}</p>
                            </div>

                            <!-- Misi toggle -->
                            <div class="mb-4">
                                <button
                                    @click="toggleMisi(kandidat.id)"
                                    class="text-xs font-medium flex items-center gap-1 mb-2 transition-colors"
                                    style="color:#9A6A72;"
                                >
                                    <component :is="expandedMisi[kandidat.id] ? EyeOff : Eye" class="w-3.5 h-3.5" />
                                    {{ expandedMisi[kandidat.id] ? "Sembunyikan Misi" : "Lihat Misi" }}
                                </button>
                                <div v-if="expandedMisi[kandidat.id]">
                                    <ul class="space-y-1.5">
                                        <li v-for="(m, i) in formatMisi(kandidat.misi)" :key="i" class="flex items-start gap-2 text-xs" style="color:#5A2A34;">
                                            <span class="w-4 h-4 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold mt-0.5" style="background-color:#51071B; color:#F5EFE6;">{{ i + 1 }}</span>
                                            {{ m }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Gold divider -->
                            <div class="flex items-center gap-2 my-3">
                                <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                                <span class="text-xs" style="color:#C5AA6C;">✦</span>
                                <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                            </div>

                            <!-- Vote button -->
                            <button
                                @click="selectKandidat(kandidat)"
                                class="musma-btn w-full mt-auto"
                            >
                                PILIH
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================
                 STEP 4: TERIMA KASIH
                 ============================ -->
            <div v-if="step === 4" class="relative z-10 flex flex-col items-center justify-center px-4 pt-16 pb-16 text-center max-w-xl mx-auto w-full min-h-[60vh]">

                <!-- Mandala ornament removed in favor of global -->

                <!-- Gold divider -->
                <div class="flex items-center gap-3 mb-6 w-full max-w-xs">
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                    <span style="color:#C5AA6C;">✦</span>
                    <div class="flex-1 h-px" style="background:linear-gradient(90deg,transparent,#C5AA6C,transparent);"></div>
                </div>

                <h1 class="musma-section-title text-4xl md:text-5xl mb-3">Terima Kasih!</h1>
                <p class="text-lg font-semibold mb-6" style="color:#DA8278;">Suara Anda Telah Direkam</p>
                <p class="text-sm leading-relaxed mb-8" style="color:#5A2A34;">
                    Terima kasih telah berpartisipasi dalam MUSMA FMIPA {{ dayjs().year() }}. Suara Anda adalah kontribusi nyata untuk masa depan DPM FMIPA yang lebih baik.
                </p>

                <Link href="/" class="musma-btn px-8 py-3 mb-8">
                    &larr; Kembali ke Beranda
                </Link>

                <div class="border-t w-full pt-6 mt-2" style="border-color:#E3D7D9;">
                    <p class="text-sm flex items-center justify-center gap-2 mb-2" style="color:#9A6A72;">
                        <img src="/gembok.png" alt="" class="w-4 h-4 opacity-60" />
                        Sesi pemilihan Anda telah berakhir.
                    </p>
                    <p class="text-sm" style="color:#9A6A72;">Anda tidak dapat melakukan pemilihan ulang.</p>
                    <p class="text-sm font-bold mt-4" style="color:#51071B;">🏛 MUSMA LM FMIPA {{ dayjs().year() }}</p>
                </div>
            </div>

        </div>

        <!-- ============================
             KONFIRMASI DIALOG OVERLAY
             ============================ -->
        <Teleport to="body">
            <div v-if="showConfirm" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                <!-- Blurred backdrop -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="cancelConfirm"></div>

                <!-- Modal -->
                <div class="relative musma-card w-full max-w-sm shadow-2xl p-7 text-center">
                    <!-- Candidate number -->
                    <div class="musma-num-badge mx-auto mb-5 !w-12 !h-12 !text-lg shadow-lg">
                        {{ selectedKandidat?.no_urut }}
                    </div>

                    <h2 class="musma-section-title text-xl mb-2">Konfirmasi Pilihan</h2>
                    <p class="text-sm mb-1" style="color:#5A2A34;">Apakah Anda yakin ingin memilih</p>
                    <p class="font-bold text-base mb-1" style="color:#51071B;">
                        <span v-for="(mhs, i) in selectedKandidat?.mahasiswa" :key="mhs.nim">
                            {{ mhs.nama }}<span v-if="i < (selectedKandidat?.mahasiswa?.length ?? 0) - 1"> &amp; </span>
                        </span>
                    </p>
                    <p class="text-sm mb-5" style="color:#5A2A34;">sebagai Ketua DPM FMIPA?</p>

                    <!-- Warning box -->
                    <div class="musma-warning-box mb-6 text-left">
                        <TriangleAlert class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:#DA8278;" />
                        <span>Tindakan ini <strong>tidak dapat diubah</strong> setelah dikonfirmasi.</span>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-center gap-3">
                        <button @click="cancelConfirm" class="musma-btn-outline flex-1 py-2.5 flex items-center justify-center text-center" :disabled="isProcessing">Batal</button>
                        <button @click="confirmVote" class="musma-btn flex-1 py-2.5 flex items-center justify-center text-center" :disabled="isProcessing">
                            <LoaderCircle v-if="isProcessing" class="h-4 w-4 animate-spin mr-2" />
                            <span v-else>Ya, Yakin</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ============================
             KONFIRMASI MASUK BILIK SUARA OVERLAY
             ============================ -->
        <Teleport to="body">
            <div v-if="showStartConfirm" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                <!-- Blurred backdrop -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showStartConfirm = false"></div>

                <!-- Modal -->
                <div class="relative musma-card w-full max-w-sm shadow-2xl p-7 text-center">
                    <!-- Warning icon -->
                    <div class="w-12 h-12 rounded-full mx-auto mb-5 flex items-center justify-center" style="background:rgba(81,7,27,0.1);">
                        <TriangleAlert class="w-6 h-6 mt-0.5" style="color:#51071B;" />
                    </div>

                    <h2 class="musma-section-title text-xl mb-3">Perhatian</h2>
                    <p class="text-sm mb-6 leading-relaxed px-2" style="color:#5A2A34;">Apakah Anda telah membaca, memahami, dan menyetujui seluruh Syarat &amp; Ketentuan pemilihan ini?</p>

                    <!-- Buttons -->
                    <div class="flex items-center justify-center gap-3">
                        <button @click="showStartConfirm = false" class="musma-btn-outline flex-1 py-2.5 flex items-center justify-center text-center">Belum</button>
                        <button @click="confirmStartVote" class="musma-btn flex-1 py-2.5 flex items-center justify-center text-center">
                            Ya, Setuju
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>
