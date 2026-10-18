<script setup lang="ts">
import { computed, h } from 'vue';
import {
  ArrowLeft,
  ArrowRight,
  ChevronDown,
  ChevronUp,
  Eye,
  EyeOff,
  Flag,
  LoaderCircle,
  Lock,
  Menu,
  Moon,
  RefreshCw,
  ShieldCheck,
  Sun,
  TriangleAlert,
  User,
} from 'lucide-vue-next';

interface Props {
  icon: string;
  filled?: boolean;
  size?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
  filled: false,
  size: 'inherit',
});

// Inline SVG component for how_to_vote (exact ballot box glyph)
const HowToVoteIcon = (iconProps: Record<string, any>) =>
  h(
    'svg',
    {
      xmlns: 'http://www.w3.org/2000/svg',
      viewBox: '0 0 24 24',
      ...iconProps,
      fill: 'currentColor',
      stroke: 'none',
      'stroke-width': 0,
    },
    [
      h('path', {
        d: 'M18 13h-.68l-2 2h2.68v4H6v-4h2.68l-2-2H6c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-4c0-1.1-.9-2-2-2zm-6-2.58l3.29-3.29c.39-.39.39-1.02 0-1.41l-2.83-2.83a.9959.9959 0 0 0-1.41 0L4.71 9.24c-.39.39-.39 1.02 0 1.41l2.83 2.83c.39.39 1.02.39 1.41 0L12 10.42zm-1.41-5.66l1.41 1.41-5.66 5.66-1.41-1.41 5.66-5.66z',
      }),
    ]
  );

const iconMap: Record<string, any> = {
  arrow_back: ArrowLeft,
  arrow_forward: ArrowRight,
  dark_mode: Moon,
  expand_less: ChevronUp,
  expand_more: ChevronDown,
  flag: Flag,
  how_to_vote: HowToVoteIcon,
  light_mode: Sun,
  lock: Lock,
  menu: Menu,
  person: User,
  progress_activity: LoaderCircle,
  refresh: RefreshCw,
  verified_user: ShieldCheck,
  visibility: Eye,
  visibility_off: EyeOff,
  warning: TriangleAlert,
};

const dimension = computed(() => {
  if (typeof props.size === 'number') return `${props.size}px`;
  if (props.size === 'xs') return '14px';
  if (props.size === 'sm') return '18px';
  if (props.size === 'md') return '24px';
  if (props.size === 'lg') return '32px';
  if (props.size === 'xl') return '40px';
  return props.size === 'inherit' ? '1em' : props.size;
});

const resolvedIcon = computed(() => iconMap[props.icon?.trim()] || null);
</script>

<template>
  <component
    :is="resolvedIcon"
    v-if="resolvedIcon"
    :size="dimension"
    class="inline-block select-none shrink-0 leading-none align-middle"
    :style="{ width: dimension, height: dimension }"
    :fill="filled ? 'currentColor' : 'none'"
    :stroke="filled ? 'none' : 'currentColor'"
    :stroke-width="filled ? 0 : 2"
    aria-hidden="true"
  />
</template>
