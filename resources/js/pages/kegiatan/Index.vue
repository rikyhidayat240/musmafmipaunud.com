<script setup lang="ts">
import { columns } from './columns';
import { Head } from '@inertiajs/vue3';
import DataTable from './DataTable.vue';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ProgramStudi, Kegiatan } from '@/types';

const { kegiatan, programStudi } = defineProps<{
    kegiatan: Kegiatan[];
    programStudi: ProgramStudi[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kegiatan',
        href: '/events',
    },
];
</script>

<template>
    <Head title="Kegiatan" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Hero Header -->
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #51071B 0%, #3B0312 100%); min-height: 120px;">
            <img src="/image 62.png" class="absolute top-0 left-0 w-28 md:w-44 h-auto object-contain opacity-20 rotate-180 select-none pointer-events-none" alt="" />
            <img src="/image 63.png" class="absolute top-0 right-0 w-16 md:w-28 h-auto object-contain opacity-20 rotate-180 select-none pointer-events-none" alt="" />
            <img src="/image 61.png" class="absolute -bottom-4 right-1/3 h-24 w-auto opacity-10 select-none pointer-events-none" alt="" />
            <div class="relative z-10 px-8 py-7">
                <p class="text-xs font-bold tracking-widest uppercase mb-1" style="color:rgba(235,206,103,0.6);">Admin Panel · MUSMA LM FMIPA</p>
                <h1 class="text-2xl md:text-3xl font-bold text-white" style="font-family:'Playfair Display',serif;">Data Kegiatan</h1>
                <p class="text-sm mt-1" style="color:rgba(245,239,230,0.55);">Kelola data kegiatan pemilihan DPM FMIPA</p>
            </div>
        </div>

        <!-- Content -->
        <div class="w-full md:max-w-7xl mx-auto px-4 py-8">
            <!-- Decorative divider -->
            <div class="flex items-center gap-3 mb-6">
                <div class="h-px flex-1" style="background:linear-gradient(90deg, rgba(81,7,27,0.15), transparent);"></div>
                <img src="/image 61.png" class="h-6 w-auto opacity-40" alt="" />
                <div class="h-px flex-1" style="background:linear-gradient(90deg, transparent, rgba(81,7,27,0.15));"></div>
            </div>

            <!-- DataTable card wrapper -->
            <div class="rounded-2xl overflow-hidden shadow-xl" style="background:#FFF8F8; border:1px solid #E3D7D9;">
                <div class="h-1" style="background:linear-gradient(90deg,#51071B,#8B1A2E,#C5AA6C);"></div>
                <div class="p-6">
                    <DataTable :columns="columns" :data="kegiatan" :helpers="{
                        programStudi: programStudi
                    }" />
                </div>
            </div>
        </div>

    </AppLayout>
</template>