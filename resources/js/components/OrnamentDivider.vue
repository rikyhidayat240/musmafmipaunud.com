<script setup lang="ts">
withDefaults(
  defineProps<{
    size?: "sm" | "md" | "lg";
    class?: string;
  }>(),
  {
    size: "md",
    class: "",
  }
);
</script>

<template>
  <div
    class="flex items-center justify-center my-3 sm:my-5 w-full select-none pointer-events-none"
    :class="class"
  >
    <img
      v-if="size === 'sm'"
      src="/image 66.png"
      alt="Ornamen"
      class="h-5 sm:h-6 w-auto object-contain opacity-75"
    />
    <img
      v-else-if="size === 'lg'"
      src="/image 65.png"
      alt="Ornamen"
      class="h-9 sm:h-12 w-auto object-contain opacity-85"
    />
    <img
      v-else
      src="/image 65.png"
      alt="Ornamen"
      class="h-7 sm:h-9 w-auto object-contain opacity-80"
    />
  </div>
</template>
