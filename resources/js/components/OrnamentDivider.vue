<script setup lang="ts">
import { computed } from "vue";

const props = withDefaults(
  defineProps<{
    size?: "sm" | "md" | "lg";
    variant?: "small" | "large" | "sm" | "md" | "lg";
    class?: string;
  }>(),
  {
    class: "",
  }
);

const resolvedSize = computed(() => {
  if (props.size) return props.size;
  if (props.variant === "small" || props.variant === "sm") return "sm";
  if (props.variant === "large" || props.variant === "lg") return "lg";
  return "md";
});
</script>

<template>
  <div
    class="flex items-center justify-center my-3 sm:my-5 w-full select-none pointer-events-none"
    :class="class"
  >
    <img
      v-if="resolvedSize === 'sm'"
      src="/image 66.webp"
      alt="Ornamen"
      width="120"
      height="24"
      loading="lazy"
      decoding="async"
      class="h-5 sm:h-6 w-auto object-contain opacity-75"
    />
    <img
      v-else-if="resolvedSize === 'lg'"
      src="/image 66.webp"
      alt="Ornamen"
      width="180"
      height="36"
      loading="lazy"
      decoding="async"
      class="h-7 sm:h-9 w-auto object-contain opacity-85"
    />
    <img
      v-else
      src="/image 66.webp"
      alt="Ornamen"
      width="150"
      height="32"
      loading="lazy"
      decoding="async"
      class="h-6 sm:h-8 w-auto object-contain opacity-80"
    />
  </div>
</template>
