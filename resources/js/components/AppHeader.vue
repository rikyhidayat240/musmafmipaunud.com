<script setup lang="ts">
import AppLogo from "@/components/AppLogo.vue";
import AppLogoIcon from "@/components/AppLogoIcon.vue";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet";
import UserMenuContent from "@/components/UserMenuContent.vue";
import { getInitials } from "@/composables/useInitials";
import type { BreadcrumbItem } from "@/types";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";
import dayjs from "dayjs";
import { Menu } from "lucide-vue-next";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";

interface Props {
  breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const adminNavItems = [
  { title: "Kandidat", href: "/candidates" },
  { title: "Hasil DPM", href: "/result" },
  { title: "Kegiatan", href: "/events" },
  { title: "Mahasiswa", href: "/users" },
];

const isActive = (href: string) => page.url === href;
</script>

<template>
  <div class="sticky top-0 z-50" style="background-color:#51071B;">
    <div class="mx-auto flex h-16 items-center justify-between px-4 md:px-8 max-w-7xl">

      <!-- Left: Logo -->
      <Link href="/" class="flex items-center gap-3 group">
        <div class="flex items-center gap-2">
          <img src="/logo 2026.png" alt="Logo" class="h-10 w-auto object-contain" />
          <div class="flex flex-col leading-tight">
            <span class="text-xs font-medium uppercase tracking-widest text-white/60 group-hover:text-white/80 transition-colors">Dewan Perwakilan Mahasiswa</span>
            <span class="text-sm font-bold text-white group-hover:text-white/90 transition-colors">MUSMA LM FMIPA {{ dayjs().year() }}</span>
          </div>
        </div>
      </Link>

      <!-- Center: Admin nav (desktop) -->
      <nav v-if="auth.user && auth.user.is_admin" class="hidden lg:flex items-center gap-6">
        <Link
          v-for="item in adminNavItems"
          :key="item.title"
          :href="item.href"
          class="text-sm font-medium transition-colors"
          :class="isActive(item.href) ? 'text-[#EBCE67]' : 'text-white/80 hover:text-white'"
        >
          {{ item.title }}
        </Link>
      </nav>

      <!-- Right: User / Login button -->
      <div class="flex items-center gap-3">
        <!-- Mobile admin menu -->
        <div v-if="auth.user && auth.user.is_admin" class="lg:hidden">
          <Sheet>
            <SheetTrigger :as-child="true">
              <Button variant="ghost" size="icon" class="text-white hover:bg-white/10 h-9 w-9">
                <Menu class="h-5 w-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" class="w-[260px] p-0" style="background-color:#51071B; border-color:rgba(235,206,103,0.2);">
              <SheetTitle class="sr-only">Menu</SheetTitle>
              <SheetHeader class="p-6 border-b border-white/10">
                <div class="flex items-center gap-2">
                  <img src="/logo 2026.png" alt="Logo" class="h-8 w-auto object-contain" />
                  <span class="text-sm font-bold text-white">MUSMA FMIPA {{ dayjs().year() }}</span>
                </div>
              </SheetHeader>
              <nav class="p-4 space-y-1">
                <Link
                  v-for="item in adminNavItems"
                  :key="item.title"
                  :href="item.href"
                  class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                  :class="isActive(item.href) ? 'bg-white/10 text-[#EBCE67]' : 'text-white/80 hover:bg-white/10 hover:text-white'"
                >
                  {{ item.title }}
                </Link>
              </nav>
            </SheetContent>
          </Sheet>
        </div>

        <!-- Logged-in user avatar dropdown -->
        <DropdownMenu v-if="auth.user">
          <DropdownMenuTrigger :as-child="true">
            <button class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 transition-colors hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-[#EBCE67]/40">
              <Avatar class="size-8 overflow-hidden rounded-full border-2 border-[#EBCE67]/40">
                <AvatarImage v-if="auth.user.avatar" :src="`/storage/${auth.user.avatar}`" :alt="auth.user.nama" />
                <AvatarFallback class="rounded-full text-xs font-bold" style="background-color:#3B0312; color:#EBCE67;">
                  {{ getInitials(auth.user?.nama) }}
                </AvatarFallback>
              </Avatar>
              <span class="hidden sm:block text-sm font-medium text-white/90">{{ auth.user.nama }}</span>
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56" style="background-color:#FFF8F8; border-color:#E3D7D9;">
            <UserMenuContent :user="auth.user" />
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Guest: Login button -->
        <div v-else>
          <Link :href="route('login')">
            <button class="text-sm font-semibold px-4 py-1.5 rounded-full border transition-colors"
              style="border-color:rgba(235,206,103,0.6); color:#EBCE67; background-color:transparent;"
              onmouseenter="this.style.backgroundColor='rgba(235,206,103,0.12)'"
              onmouseleave="this.style.backgroundColor='transparent'"
            >
              Login Pemilih
            </button>
          </Link>
        </div>
      </div>

    </div>
  </div>
</template>
