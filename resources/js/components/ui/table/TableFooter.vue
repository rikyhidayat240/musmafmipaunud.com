<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <tfoot :class="cn('border-t border-[#E3D7D9] dark:border-[rgba(252,206,136,0.1)] bg-[#FAF4ED] dark:bg-[#25050D] text-[#3B0613] dark:text-[#FAF4EB] font-medium [&>tr]:last:border-b-0', props.class)">
    <slot />
  </tfoot>
</template>
