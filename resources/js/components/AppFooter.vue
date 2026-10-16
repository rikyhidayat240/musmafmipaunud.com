<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import dayjs from "dayjs";
</script>

<template>
  <footer class="w-full bg-[#3B0613] dark:bg-[#140206] text-white relative z-40 overflow-hidden border-t border-white/10 dark:border-[#FCCE88]/20 transition-colors">
    <!-- Subtle Corner Flourishes inside footer -->
    <img
      src="/image 62.png"
      alt=""
      class="absolute -bottom-8 -left-8 w-28 sm:w-36 h-auto opacity-15 pointer-events-none select-none"
    />
    <img
      src="/image 63.png"
      alt=""
      class="absolute -bottom-8 -right-8 w-28 sm:w-36 h-auto opacity-15 pointer-events-none select-none rotate-180"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-6 sm:py-8 flex flex-col items-center justify-center text-center relative z-10 gap-2">
      <!-- Center: Logo + Organization Info -->
      <Link href="/" class="flex items-center gap-3 group">
        <img
          src="/logo 2026.png"
          alt="Logo DPM"
          class="h-9 sm:h-10 w-auto object-contain shrink-0 opacity-90 group-hover:opacity-100 transition-opacity"
        />
        <div class="flex flex-col text-left leading-tight">
          <span class="text-xs sm:text-sm font-bold text-white group-hover:text-[#FCCE88] transition-colors">
            DPM FMIPA · MUSMA LM FMIPA {{ dayjs().year() }}
          </span>
          <span class="text-[11px] sm:text-xs text-white/60">
            Dewan Perwakilan Mahasiswa · Fakultas Matematika dan Ilmu Pengetahuan Alam
          </span>
        </div>
      </Link>

      <!-- Copyright -->
      <p class="text-[10px] sm:text-xs text-white/40 mt-1">
        © {{ dayjs().year() }} DPM FMIPA Universitas Udayana. Hak cipta dilindungi.
      </p>
    </div>
  </footer>
</template>
