<script setup lang="ts">
import dayjs from "dayjs";
</script>

<template>
  <footer style="background-color:#51071B;" class="w-full relative z-50">
    <div class="mx-auto px-4 md:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 w-full">
      <!-- Left: Logo + name -->
      <div class="flex items-center gap-3">
        <img src="/logo 2026.png" alt="Logo MUSMA" class="h-8 w-auto object-contain opacity-80" />
        <div class="flex flex-col leading-tight">
          <span class="text-xs font-medium text-white/60 uppercase tracking-widest">MUSMA LM FMIPA {{ dayjs().year() }}</span>
          <span class="text-xs text-white/50">Dewan Perwakilan Mahasiswa - Fakultas Matematika dan Ilmu Pengetahuan Alam</span>
        </div>
      </div>
      <!-- Right: copyright -->
      <p class="text-xs text-white/40">© {{ dayjs().year() }} MUSMA LM FMIPA. All rights reserved.</p>
    </div>
  </footer>
</template>
