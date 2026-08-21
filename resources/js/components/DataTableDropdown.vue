<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { MoreVertical, Pencil, Trash2, LoaderCircle } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

// Define props
const props = defineProps<{
    data?: any;
    deleteRoute?: string;
}>();

// Dialog state
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const isDeleting = ref(false);

// Handlers
const handleDelete = () => {
    if (!props.deleteRoute) return;

    isDeleting.value = true;

    router.delete(props.deleteRoute, {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            isDeleting.value = false;
        },
        onError: () => {
            isDeleting.value = false;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0" @click.stop>
                <span class="sr-only">Open menu</span>
                <MoreVertical class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <!-- Edit Dialog with Slot -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogTrigger as-child>
                    <DropdownMenuItem class="flex items-center" @select.prevent>
                        <Pencil class="w-4 h-4 mr-1" />
                        Ubah
                    </DropdownMenuItem>
                </DialogTrigger>
                <!-- Slot untuk form edit yang dinamis -->
                <slot name="edit-form" :data="data" :close="() => isEditDialogOpen = false" />
            </Dialog>

            <!-- Delete Dialog -->
            <Dialog v-model:open="isDeleteDialogOpen">
                <DialogTrigger as-child>
                    <DropdownMenuItem variant="destructive" class="flex items-center" @select.prevent>
                        <Trash2 class="w-4 h-4 mr-1" color="hsl(0 84.2% 60.2%)" />
                        <span class="text-red-700">Hapus</span>
                    </DropdownMenuItem>
                </DialogTrigger>
                <!-- Dialog untuk konfirmasi hapus -->
                <DialogContent class="max-w-xl">
                    <DialogHeader>
                        <DialogTitle>Apakah Anda yakin ingin menghapus data ini?</DialogTitle>
                        <DialogDescription>
                            Tindakan ini tidak dapat dibatalkan. Pastikan Anda telah memeriksa kembali sebelum menghapus
                            data ini.
                        </DialogDescription>
                    </DialogHeader>

                    <!-- Dialog Actions -->
                    <div class="flex justify-end gap-2 pt-4">
                        <Button variant="outline" @click="isDeleteDialogOpen = false" :disabled="isDeleting">
                            Batal
                        </Button>
                        <Button variant="destructive" @click="handleDelete" :disabled="isDeleting">
                            <LoaderCircle v-if="isDeleting" class="h-4 w-4 animate-spin" />
                            <span class="text-red-700">Hapus</span>
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </DropdownMenuContent>
    </DropdownMenu>
</template>