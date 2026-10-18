<script setup lang="ts">
import { columns } from './columns';
import { Head } from '@inertiajs/vue3';
import DataTable from './DataTable.vue';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Kegiatan, Kandidat, User } from '@/types';

const props = defineProps<{
    kandidat: Kandidat[];
    kegiatan: Kegiatan[];
    mahasiswa: User[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kandidat',
        href: '/candidates',
    },
];
</script>

<template>
    <Head title="Kandidat" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Hero Header -->
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #51071B 0%, #3B0312 100%); min-height: 120px;">
            <img src="/image 62.webp" class="absolute top-0 left-0 w-28 md:w-44 h-auto object-contain opacity-20 rotate-180 select-none pointer-events-none" alt="" />
            <img src="/image 63.webp" class="absolute top-0 right-0 w-16 md:w-28 h-auto object-contain opacity-20 rotate-180 select-none pointer-events-none" alt="" />
            <img src="/image 61.webp" class="absolute -bottom-4 right-1/3 h-24 w-auto opacity-10 select-none pointer-events-none" alt="" />
            <div class="relative z-10 px-8 py-7">
                <p class="text-xs font-bold tracking-widest uppercase mb-1" style="color:rgba(235,206,103,0.6);">Admin Panel · MUSMA LM FMIPA</p>
                <h1 class="text-2xl md:text-3xl font-bold text-white" style="font-family:'Playfair Display',serif;">Data Kandidat</h1>
                <p class="text-sm mt-1" style="color:rgba(245,239,230,0.55);">Kelola data kandidat Pemilihan Ketua DPM FMIPA</p>
            </div>
        </div>

        <!-- Content -->
        <div class="w-full md:max-w-7xl mx-auto px-4 py-8">
            <!-- Decorative divider -->
            <div class="flex items-center gap-3 mb-6">
                <div class="h-px flex-1 bg-gradient-to-r from-[#51071B]/15 dark:from-[rgba(252,206,136,0.25)] to-transparent"></div>
                <img src="/image 61.webp" class="h-6 w-auto opacity-40 dark:opacity-60 dark:invert" alt="" />
                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-[#51071B]/15 dark:to-[rgba(252,206,136,0.25)]"></div>
            </div>

            <!-- DataTable card wrapper -->
            <div class="rounded-2xl overflow-hidden shadow-xl bg-[#FFF8F8] dark:bg-[#27060F] border border-[#E3D7D9] dark:border-[rgba(252,206,136,0.2)] dark:shadow-[0_10px_35px_rgba(0,0,0,0.5)]">
                <!-- Card top bar -->
                <div class="h-1 bg-gradient-to-r from-[#51071B] via-[#8B1A2E] to-[#C5AA6C] dark:from-[#FCCE88] dark:via-[#DA8278] dark:to-[#3B0613]"></div>
                <div class="p-6">
                    <DataTable :columns="columns" :data="kandidat" :helpers="{
                        mahasiswa: mahasiswa,
                        kegiatan: kegiatan,
                        kandidat: kandidat,
                    }" />
                </div>
            </div>
        </div>

    </AppLayout>
</template>