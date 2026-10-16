<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <tr :class="cn('border-b border-[#E3D7D9] dark:border-[rgba(252,206,136,0.1)] transition-colors hover:bg-[#FAF4ED] dark:hover:bg-[#2C0610] data-[state=selected]:bg-[#F5ECEE] dark:data-[state=selected]:bg-[#2C0610]', props.class)">
    <slot />
  </tr>
</template>
