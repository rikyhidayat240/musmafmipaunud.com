<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
  <!-- Full-page cream musma background -->
  <div class="relative min-h-svh flex flex-col items-center justify-center overflow-hidden musma-bg">

    <!-- Global Decorations -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <!-- Center Watermark (Foxes) -->
        <img src="/logo 2026.png" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] md:w-[600px] h-auto object-contain opacity-[0.04] dark:opacity-[0.06] select-none" alt="" />
        <!-- Corner Ornaments (made smaller) -->
        <img src="/image 62.png" class="absolute top-0 left-0 w-[120px] md:w-[220px] h-auto object-contain opacity-60 dark:opacity-40 rotate-180 -translate-x-1/4 -translate-y-1/4 select-none" alt="" />
        <img src="/image 63.png" class="absolute top-0 right-0 w-[90px] md:w-[180px] h-auto object-contain opacity-60 dark:opacity-40 rotate-180 translate-x-1/4 -translate-y-1/4 select-none" alt="" />
        <img src="/image 63.png" class="absolute bottom-0 left-0 w-[90px] md:w-[180px] h-auto object-contain opacity-60 dark:opacity-40 -translate-x-1/4 translate-y-1/4 select-none" alt="" />
        <img src="/image 62.png" class="absolute bottom-0 right-0 w-[120px] md:w-[220px] h-auto object-contain opacity-60 dark:opacity-40 translate-x-1/4 translate-y-1/4 select-none" alt="" />
    </div>

    <!-- Center content -->
    <div class="relative z-10 w-full max-w-md mx-auto px-4 py-10">
      <slot />
    </div>

  </div>
</template>
