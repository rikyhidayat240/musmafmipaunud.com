<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import dayjs from 'dayjs';
</script>

<template>
    <AppLogoIcon className="size-16 fill-current text-white dark:text-black" />
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold text-primary">
            Musyawarah Mahasiswa<br /> 
            FMIPA {{ dayjs().year() }}
        </span>
    </div>
</template>
