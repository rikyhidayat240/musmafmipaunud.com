<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  icon: string;
  filled?: boolean;
  size?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
  filled: false,
  size: 'inherit',
});

const computedSize = computed(() => {
  if (typeof props.size === 'number') {
    return `${props.size}px`;
  }
  if (props.size === 'xs') return '14px';
  if (props.size === 'sm') return '18px';
  if (props.size === 'md') return '24px';
  if (props.size === 'lg') return '32px';
  if (props.size === 'xl') return '40px';
  return props.size;
});
</script>

<template>
  <span
    class="material-symbols-outlined select-none inline-flex items-center justify-center leading-none shrink-0"
    :class="{ 'material-symbols-filled': filled }"
    :style="{ fontSize: computedSize }"
    aria-hidden="true"
  >
    {{ icon }}
  </span>
</template>

<style scoped>
.material-symbols-filled {
  font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
