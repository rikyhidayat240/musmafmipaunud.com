<script setup lang="ts">
import dayjs from 'dayjs';
import AppLogo from './AppLogo.vue';
import { MapPin, Phone, Mail } from 'lucide-vue-next';
</script>

<template>
    <footer class="bg-background shadow-inner w-full py-8">
        <div class="flex flex-col space-y-4 w-full md:max-w-7xl items-center mx-auto px-4">
            <div class="flex flex-col md:flex-row w-full gap-8 justify-between items-start">
                <div class="flex flex-col gap-4 md:mt-2 md:max-w-7xl">
                    <div class="flex gap-2 mb-2 md:mb-4 items-center">
                        <AppLogo />
                    </div>
                    <div class="flex gap-2 items-center text-base text-sidebar-foreground">
                        <MapPin class="size-5" />
                        <a href="https://maps.app.goo.gl/HGpJX4JiSpED4oZ57">Jl. Raya Kampus Unud, Bukit Jimbaran,
                            Bali</a>
                    </div>
                    <div class="flex gap-2 items-center text-base text-sidebar-foreground">
                        <Phone class="size-5" />
                        <a href="https://wa.me/6283851028574">+62 838-5102-8574</a>
                    </div>
                    <div class="flex gap-2 items-center text-base text-sidebar-foreground">
                        <Mail class="size-5" />
                        <a
                            href="https://mail.google.com/mail/?view=cm&fs=1&to=dpmfmipaunud2025@gmail.com">dpmfmipaunud2025@gmail.com</a>
                    </div>
                </div>
                <div className="w-full md:w-[400px] flex justify-center md:justify-end">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3942.8585412695247!2d115.16811767462733!3d-8.799357891253086!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd244bc3acab8d9%3A0x7fba454d24527b74!2sFakultas%20Matematika%20dan%20Ilmu%20Pengetahuan%20Alam!5e0!3m2!1sid!2sid!4v1760596098552!5m2!1sid!2sid"
                        className="w-full md:max-w-[800px] h-[300px] md:h-[200px] rounded-lg" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
            <div class="w-full border-t border-sidebar-foreground text-sidebar-foreground mt-4">
                <p class="text-center mt-4 font-medium">© {{ dayjs().year() }} MUSMA FMIPA. All rights reserved.</p>
            </div>
        </div>
    </footer>
</template>