<script setup lang="ts">
import { columns } from './columns';
import { Head } from '@inertiajs/vue3';
import DataTable from './DataTable.vue';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ProgramStudi, Kegiatan } from '@/types';

const { kegiatan } = defineProps<{
    kegiatan: Kegiatan[];
    programStudi: ProgramStudi[];
}>();
console.log(kegiatan);

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
        <div class="w-full md:max-w-7xl mx-auto px-4 py-6">
            <DataTable :columns="columns" :data="kegiatan" :helpers="{
                programStudi: programStudi
            }" />
        </div>
    </AppLayout>
</template>