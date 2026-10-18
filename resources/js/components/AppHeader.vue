<script setup lang="ts">
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet";
import UserMenuContent from "@/components/UserMenuContent.vue";
import { getInitials } from "@/composables/useInitials";
import type { BreadcrumbItem } from "@/types";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";
import dayjs from "dayjs";
import MaterialIcon from "@/components/MaterialIcon.vue";
import { useAppearance } from "@/composables/useAppearance";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

interface Props {
  breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const { appearance, updateAppearance } = useAppearance();

const isDark = computed(() => {
  if (appearance.value === 'dark') return true;
  if (appearance.value === 'light') return false;
  if (typeof window !== 'undefined') {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  return false;
});

const toggleTheme = () => {
  const next = isDark.value ? 'light' : 'dark';
  updateAppearance(next);
};

const adminNavItems = [
  { title: "Kandidat", href: "/candidates" },
  { title: "Hasil DPM", href: "/result" },
  { title: "Kegiatan", href: "/events" },
  { title: "Mahasiswa", href: "/users" },
];

const isActive = (href: string) => page.url === href;
</script>

<template>
  <header class="sticky top-0 z-50 w-full shadow-md bg-[#3B0613] dark:bg-[#140206] border-b border-white/10 dark:border-[#FCCE88]/20 transition-colors">
    <div class="mx-auto flex h-16 sm:h-[72px] items-center justify-between px-4 sm:px-8 max-w-7xl w-full">

      <!-- Left: Logo & Organization Name -->
      <Link href="/" class="flex items-center gap-2 sm:gap-3 group min-w-0">
        <img
          src="/logo 2026.png"
          alt="DPM FMIPA Logo"
          width="44"
          height="44"
          fetchpriority="high"
          decoding="async"
          class="h-9 sm:h-11 w-auto object-contain shrink-0 transition-transform group-hover:scale-105"
        />
        <div class="flex flex-col leading-tight min-w-0">
          <span class="text-[9px] sm:text-[11px] font-medium tracking-wider sm:tracking-[0.18em] uppercase text-white/70 truncate">
            Dewan Perwakilan Mahasiswa
          </span>
          <span class="text-xs sm:text-sm font-bold text-white group-hover:text-white/90 transition-colors truncate">
            MUSMA LM FMIPA {{ dayjs().year() }}
          </span>
        </div>
      </Link>

      <!-- Center: Decorative Diamond Divider (or Admin Nav if admin) -->
      <div v-if="!auth.user || !auth.user.is_admin" class="hidden md:flex items-center gap-3 select-none pointer-events-none opacity-40">
        <div class="w-12 h-px bg-gradient-to-r from-transparent to-white"></div>
        <span class="text-[10px] text-white">◆</span>
        <div class="w-12 h-px bg-gradient-to-l from-transparent to-white"></div>
      </div>

      <!-- Center: Admin Navigation (Desktop) -->
      <nav v-else class="hidden lg:flex items-center gap-6">
        <Link
          v-for="item in adminNavItems"
          :key="item.title"
          :href="item.href"
          class="text-xs sm:text-sm font-medium transition-colors"
          :class="isActive(item.href) ? 'text-[#FCCE88] font-semibold' : 'text-white/80 hover:text-white'"
        >
          {{ item.title }}
        </Link>
      </nav>

      <!-- Right: Theme Toggle & User Account / Login Button -->
      <div class="flex items-center gap-2.5 sm:gap-3">
        <!-- Dark/Light Theme Toggle (Google Material Icon) -->
        <button
          @click="toggleTheme"
          type="button"
          class="size-9 rounded-full text-white/80 hover:text-[#FCCE88] hover:bg-white/10 transition-all border border-white/15 dark:border-[#FCCE88]/30 flex items-center justify-center cursor-pointer shrink-0"
          :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
          aria-label="Toggle Dark Mode"
        >
          <MaterialIcon :icon="isDark ? 'light_mode' : 'dark_mode'" class="text-xl leading-none text-white hover:text-[#FCCE88]" />
        </button>

        <!-- Mobile Admin Menu Sheet -->
        <div v-if="auth.user && auth.user.is_admin" class="lg:hidden">
          <Sheet>
            <SheetTrigger :as-child="true">
              <button class="text-white hover:bg-white/10 h-9 w-9 rounded-lg flex items-center justify-center transition-colors">
                <MaterialIcon icon="menu" class="text-xl" />
              </button>
            </SheetTrigger>
            <SheetContent side="right" class="w-[260px] p-0 bg-[#3B0613] dark:bg-[#140206] border-white/15 dark:border-[#FCCE88]/20 text-white">
              <SheetTitle class="sr-only">Menu Navigasi</SheetTitle>
              <SheetHeader class="p-6 border-b border-white/10">
                <div class="flex items-center gap-2.5">
                  <img src="/logo 2026.png" alt="Logo" width="32" height="32" loading="lazy" decoding="async" class="h-8 w-auto object-contain" />
                  <span class="text-sm font-bold text-white">MUSMA FMIPA {{ dayjs().year() }}</span>
                </div>
              </SheetHeader>
              <nav class="p-4 space-y-1">
                <Link
                  v-for="item in adminNavItems"
                  :key="item.title"
                  :href="item.href"
                  class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                  :class="isActive(item.href) ? 'bg-white/10 text-[#FCCE88] font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white'"
                >
                  {{ item.title }}
                </Link>
              </nav>
            </SheetContent>
          </Sheet>
        </div>

        <!-- Authenticated User Profile Pill (Figma: initials circle + name) -->
        <DropdownMenu v-if="auth.user">
          <DropdownMenuTrigger :as-child="true">
            <button class="flex items-center gap-2 sm:gap-2.5 rounded-full p-1 sm:py-1 sm:pl-1 sm:pr-3.5 transition-all hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-[#FCCE88]/40 border border-white/10 dark:border-[#FCCE88]/30">
              <div class="size-8 rounded-full flex items-center justify-center font-bold text-xs bg-[#E5D9C3] text-[#3B0613] shadow-sm shrink-0">
                {{ getInitials(auth.user?.nama) }}
              </div>
              <span class="hidden sm:inline text-xs sm:text-sm font-semibold text-white max-w-[180px] truncate">
                {{ auth.user.nama }}
              </span>
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56 bg-[#FFFDFB] dark:bg-[#27060F] border-[#3B0613]/15 dark:border-[#FCCE88]/25 shadow-xl text-[#3B0613] dark:text-[#FAF4EB]">
            <UserMenuContent :user="auth.user" />
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Guest: "Masuk" Button -->
        <div v-else>
          <Link :href="route('login')">
            <button
              class="text-xs sm:text-sm font-bold px-5 py-2 rounded-full border border-white/20 bg-[#50071B] dark:bg-[#FCCE88] hover:bg-[#5E0C22] dark:hover:bg-[#E2B771] text-white dark:text-[#2A050E] shadow-sm transition-all active:scale-[0.98] cursor-pointer"
            >
              Masuk
            </button>
          </Link>
        </div>
      </div>

    </div>
  </header>
</template>
