<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <caption :class="cn('mt-4 text-sm text-[#7D6B6F] dark:text-[#E2D5D7]', props.class)">
    <slot />
  </caption>
</template>
