<script setup lang="ts">
import dayjs from "dayjs";

withDefaults(
  defineProps<{
    year?: number | string;
    class?: string;
  }>(),
  {
    year: () => dayjs().year(),
    class: "",
  }
);
</script>

<template>
  <div class="relative w-full max-w-[440px] sm:max-w-[520px] flex items-center justify-center select-none py-4 sm:py-8" :class="class">
    <!-- Concentric Radial Glow Aura -->
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden">
      <!-- Glow background -->
      <div
        class="w-[280px] sm:w-[460px] h-[280px] sm:h-[460px] rounded-full opacity-70 shrink-0"
        style="background: radial-gradient(circle, rgba(218, 130, 120, 0.35) 0%, rgba(245, 206, 175, 0.18) 45%, transparent 70%);"
      ></div>
      <!-- Thin concentric circles -->
      <div class="absolute w-[250px] sm:w-[400px] h-[250px] sm:h-[400px] rounded-full border border-[#DA8278]/25 shrink-0"></div>
      <div class="absolute w-[290px] sm:w-[470px] h-[290px] sm:h-[470px] rounded-full border border-[#DA8278]/15 shrink-0"></div>
    </div>

    <!-- Container for floating ballots + box -->
    <div class="relative w-full max-w-[300px] sm:max-w-[420px] flex flex-col items-center px-1">
      <!-- Floating Ballots Section (Clearly floating above the ballot box) -->
      <div class="relative w-full max-w-[280px] sm:max-w-[390px] flex justify-between px-2 sm:px-6 mb-4 sm:mb-6 z-20">
        <!-- Ballot 01 (Left, Terracotta) -->
        <div class="relative flex flex-col items-center">
          <div
            class="animate-float-ballot-1 w-20 sm:w-28 h-28 sm:h-36 rounded-xl border-2 sm:border-[2.5px] border-white/90 dark:border-[#FCCE88]/60 shadow-2xl flex flex-col items-center justify-between p-2 sm:p-3 text-white cursor-pointer hover:scale-105 transition-all shrink-0"
            style="background: linear-gradient(145deg, #DA8278, #C4685E); box-shadow: 0 16px 32px rgba(218, 130, 120, 0.35);"
          >
            <div class="w-8 sm:w-12 h-8 sm:h-12 rounded-full bg-[#3B0613]/90 dark:bg-[#1A0308] flex items-center justify-center shadow-inner">
              <div class="w-4 sm:w-5 h-4 sm:h-5 rounded-full bg-white/20"></div>
            </div>
            <div class="w-full flex flex-col items-center gap-0.5 sm:gap-1">
              <div class="w-8 sm:w-12 h-1 rounded-full bg-white/40"></div>
              <div class="w-5 sm:w-8 h-1 rounded-full bg-white/30"></div>
            </div>
            <span class="font-serif font-extrabold text-xl sm:text-3xl text-white tracking-wider">01</span>
          </div>
          <!-- Soft ambient float shadow below ballot 01 -->
          <div class="w-14 sm:w-20 h-2 rounded-full bg-black/20 dark:bg-[#EBCE67]/10 blur-sm mt-2 animate-float-shadow-1"></div>
        </div>

        <!-- Ballot 02 (Right, Ochre/Gold) -->
        <div class="relative flex flex-col items-center">
          <div
            class="animate-float-ballot-2 w-20 sm:w-28 h-28 sm:h-36 rounded-xl border-2 sm:border-[2.5px] border-white/90 dark:border-[#FCCE88]/80 shadow-2xl flex flex-col items-center justify-between p-2 sm:p-3 text-[#3B0613] cursor-pointer hover:scale-105 transition-all shrink-0"
            style="background: linear-gradient(145deg, #F3CE8A, #D6A85B); box-shadow: 0 16px 32px rgba(243, 206, 138, 0.35);"
          >
            <div class="w-8 sm:w-12 h-8 sm:h-12 rounded-full bg-[#3B0613] dark:bg-[#1A0308] flex items-center justify-center shadow-inner">
              <div class="w-4 sm:w-5 h-4 sm:h-5 rounded-full bg-white/20"></div>
            </div>
            <div class="w-full flex flex-col items-center gap-0.5 sm:gap-1">
              <div class="w-8 sm:w-12 h-1 rounded-full bg-[#3B0613]/30"></div>
              <div class="w-5 sm:w-8 h-1 rounded-full bg-[#3B0613]/20"></div>
            </div>
            <span class="font-serif font-extrabold text-xl sm:text-3xl text-[#3B0613] tracking-wider">02</span>
          </div>
          <!-- Soft ambient float shadow below ballot 02 -->
          <div class="w-14 sm:w-20 h-2 rounded-full bg-black/20 dark:bg-[#EBCE67]/10 blur-sm mt-2 animate-float-shadow-2"></div>
        </div>
      </div>

      <!-- 3D Kotak Suara (Voting Box) -->
      <div class="relative w-full max-w-[290px] sm:max-w-[420px] z-20">
        <!-- Top Lid with Slot -->
        <div
          class="w-full rounded-2xl border-2 sm:border-[3px] border-white px-3 sm:px-6 py-2.5 sm:py-3 shadow-xl relative overflow-hidden"
          style="background: linear-gradient(180deg, #5A0C22 0%, #3B0613 100%);"
        >
          <!-- Shiny bevel line -->
          <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-white/40 to-transparent"></div>

          <!-- Ballot Slot -->
          <div class="w-full h-2.5 sm:h-3 rounded-full bg-[#1F0208] border border-[#FCCE88]/40 shadow-inner mx-auto mb-1 sm:mb-1.5 flex items-center justify-center">
            <div class="w-1/3 h-0.5 bg-black/80 rounded-full"></div>
          </div>

          <!-- Slot label text -->
          <p class="text-center text-[8px] sm:text-[10px] uppercase tracking-[0.2em] sm:tracking-[0.25em] font-semibold text-white/70">
            MASUKKAN SURAT SUARA
          </p>
        </div>

        <!-- Box Front Body -->
        <div
          class="w-[96%] mx-auto -mt-1.5 rounded-b-2xl border-x-2 border-b-2 sm:border-x-[3px] sm:border-b-[3px] border-white p-4 sm:p-7 shadow-2xl relative overflow-hidden flex flex-col items-center text-center"
          style="background: linear-gradient(135deg, #4A0818 0%, #3B0613 50%, #250209 100%);"
        >
          <!-- Glass glare streak -->
          <div
            class="absolute -top-16 -right-16 w-44 h-64 rotate-45 pointer-events-none opacity-20"
            style="background: linear-gradient(180deg, rgba(255,255,255,0.6) 0%, transparent 100%);"
          ></div>

          <!-- Circular Gold DPM Seal -->
          <div class="w-14 sm:w-20 h-14 sm:h-20 rounded-full border border-[#EBCE67]/70 flex items-center justify-center mb-2 sm:mb-2.5 relative shadow-inner">
            <div class="w-[90%] h-[90%] rounded-full border border-dashed border-[#EBCE67]/40 flex flex-col items-center justify-center select-none leading-none">
              <span class="text-[8px] sm:text-[10px] text-[#FCCE88] leading-none mb-0.5">✦</span>
              <span class="font-serif text-[#FCCE88] font-bold text-[11px] sm:text-sm tracking-widest leading-none my-0.5">DPM</span>
              <span class="text-[8px] sm:text-[10px] text-[#FCCE88] leading-none mt-0.5">✦</span>
            </div>
          </div>

          <!-- Title -->
          <h3 class="font-serif font-bold text-white text-lg sm:text-2xl tracking-wide drop-shadow-sm">
            Kotak Suara
          </h3>

          <!-- Subtitle -->
          <p class="text-[11px] sm:text-sm text-white/60 tracking-widest uppercase mt-0.5">
            PEMIRA FMIPA · {{ year }}
          </p>

          <!-- Bottom Sealed Dot Badge -->
          <div class="w-full flex justify-end items-center gap-1.5 mt-3 sm:mt-5 pr-1">
            <div class="w-2 h-2 rounded-full bg-[#DA8278] shadow-[0_0_8px_#DA8278]"></div>
            <span class="text-[8px] sm:text-[10px] uppercase font-bold tracking-widest text-white/50">
              TERSEGEL
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
