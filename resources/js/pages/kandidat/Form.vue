<script setup lang="ts">
import { ref, computed } from 'vue';
import { cn } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import { User, Kegiatan, Kandidat } from '@/types';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { AspectRatio } from '@/components/ui/aspect-ratio';
import { DialogDescription, DialogHeader, DialogTitle, DialogContent } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

// define props
const props = defineProps<{
  mode: 'view' | 'create' | 'edit';
  mahasiswa: User[];
  kegiatan: Kegiatan[];
  kandidat: Kandidat[];
  kandidatData?: Kandidat;
}>();

// define emits
const emit = defineEmits<{
  success: []
}>();

// define form fields
const form = useForm({
  id: props.kandidatData?.id || null,
  id_kegiatan: props.kandidatData?.id_kegiatan || null,
  no_urut: props.kandidatData?.no_urut || '',
  visi: props.kandidatData?.visi || '',
  misi: props.kandidatData?.misi || '',
  nim_ketua: props.kandidatData?.mahasiswa?.find(m => m.pivot.jabatan === 'ketua')?.nim || null,
  foto: null as File | null,
});

// State untuk search
const searchKetua = ref('');
const showKetuaSuggestions = ref(false);

// Filter mahasiswa yang sudah menjadi kandidat di kegiatan yang sama
const availableMahasiswaForKetua = computed(() => {
  if (!form.id_kegiatan) return props.mahasiswa;

  const existingKandidatNim = props.kandidat
    .filter(k => k.id_kegiatan === form.id_kegiatan && k.id !== form.id)
    .flatMap(k => k.mahasiswa?.map(m => m.nim) || []);

  return props.mahasiswa.filter(m => !existingKandidatNim.includes(m.nim));
});

// Filtered suggestions berdasarkan search
const filteredKetuaSuggestions = computed(() => {
  if (!searchKetua.value) return availableMahasiswaForKetua.value;
  const search = searchKetua.value.toLowerCase();
  return availableMahasiswaForKetua.value.filter(m =>
    m.nama.toLowerCase().includes(search) ||
    m.nim.toLowerCase().includes(search)
  );
});


// Reset wakil ketika kegiatan berubah dan bukan fakultas
const handleKegiatanChange = (value: any) => {
  form.id_kegiatan = value;
  form.nim_ketua = null;
  searchKetua.value = '';
};

// Get mahasiswa name by NIM
const getMahasiswaName = (nim: string | null) => {
  if (!nim) return '';
  const mahasiswa = props.mahasiswa.find(m => m.nim === nim);
  return mahasiswa ? `${mahasiswa.nama} (${mahasiswa.nim})` : '';
};

// Handle selection
const selectKetua = (mahasiswa: User) => {
  form.nim_ketua = mahasiswa.nim;
  searchKetua.value = `${mahasiswa.nama} (${mahasiswa.nim})`;
  showKetuaSuggestions.value = false;
};

// Handle blur with delay
const handleKetuaBlur = () => {
  window.setTimeout(() => {
    showKetuaSuggestions.value = false;
  }, 200);
};

// Initialize search values
if ((props.mode === 'edit' || props.mode === 'view') && props.kandidatData) {
  searchKetua.value = getMahasiswaName(form.nim_ketua);
}

// Foto preview state
const fotoPreview = ref<string | null>(null);

// Handle foto change
const handleFotoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    form.foto = file;
    fotoPreview.value = URL.createObjectURL(file);
  }
};

// submit function
const submit = () => {
  const transformedData = {
    ...form.data(),
    mahasiswa: [
      { nim: form.nim_ketua, jabatan: 'ketua' },
      // ...(isKegiatanFakultas.value ? [{ nim: form.nim_wakil, jabatan: 'wakil' }] : [])
    ].filter(m => m.nim)
  };

  switch (props.mode) {
    case 'create':
      form.transform(() => transformedData).post(route('candidates.store'), {
        forceFormData: true,
        onSuccess: () => {
          emit('success');
          form.reset();
          fotoPreview.value = null;
          searchKetua.value = '';
        },
      });
      break;
    case 'edit':
      form.transform((data) => ({
        ...transformedData,
        _method: 'PUT'
      })).post(route('candidates.update', props.kandidatData?.id), {
        forceFormData: true,
        onSuccess: () => {
          emit('success');
          fotoPreview.value = null;
        },
      });
      break;
  }
};

</script>

<template>
  <DialogContent class="max-w-4xl max-h-[90vh] flex flex-col p-6 overflow-hidden">
    <!-- Dialog Header -->
    <DialogHeader class="shrink-0 pb-3 border-b border-border/40">
      <DialogTitle class="text-lg font-bold text-[#3B0613] dark:text-[#FCCE88]">
        {{ mode === 'create' ? 'Tambah' : mode === 'edit' ? 'Ubah' : 'Detail' }} Data Kandidat
      </DialogTitle>
      <DialogDescription class="text-sm text-muted-foreground dark:text-[#FAF4EB]/70">
        {{ mode === 'create'
          ? 'Isi form berikut untuk menambahkan data kandidat baru.'
          : mode === 'edit'
            ? 'Ubah data kandidat yang diperlukan pada form di bawah.'
            : 'Lihat detail lengkap data kandidat yang dipilih.'
        }}
      </DialogDescription>
    </DialogHeader>

    <!-- Form Content -->
    <form method="POST" @submit.prevent="submit" class="flex flex-col flex-1 min-h-0">
      <div class="mt-4 overflow-y-auto max-h-[calc(90vh-11rem)] pr-3 space-y-5">
        <!-- Top Section: Data Utama & Foto -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
          <!-- Kolom Kiri: Kegiatan, No Urut, Ketua (7 cols) -->
          <div class="md:col-span-7 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <!-- Kegiatan -->
              <div class="sm:col-span-2 space-y-1.5">
                <Label for="id_kegiatan">
                  Kegiatan
                  <span v-if="mode !== 'view'" class="text-red-500">*</span>
                </Label>
                <Select :model-value="form.id_kegiatan" @update:model-value="handleKegiatanChange" :tabindex="1"
                  :disabled="form.processing || mode === 'view'" :required="mode !== 'view'">
                  <SelectTrigger id="id_kegiatan">
                    <SelectValue placeholder="Pilih Kegiatan" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="k in kegiatan" :key="k.id" :value="k.id">
                      {{ k.nama.replace('Kegiatan ', '') }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.id_kegiatan" />
              </div>

              <!-- Nomor Urut -->
              <div class="sm:col-span-1 space-y-1.5">
                <Label for="no_urut">
                  No. Urut
                  <span v-if="mode !== 'view'" class="text-red-500">*</span>
                </Label>
                <Input id="no_urut" type="text" :tabindex="2" v-model="form.no_urut" :required="mode !== 'view'"
                  :disabled="form.processing || mode === 'view'" :readonly="mode === 'view'" placeholder="Contoh: 01" />
                <InputError :message="form.errors.no_urut" />
              </div>
            </div>

            <!-- Ketua with Search Input -->
            <div class="space-y-1.5 relative">
              <Label for="nim_ketua">
                Ketua
                <span v-if="mode !== 'view'" class="text-red-500">*</span>
              </Label>
              <Input id="nim_ketua" type="text" :tabindex="3" v-model="searchKetua" @focus="showKetuaSuggestions = true"
                @blur="handleKetuaBlur"
                :disabled="form.processing || mode === 'view' || !form.id_kegiatan" :readonly="mode === 'view'"
                placeholder="Cari nama atau NIM mahasiswa..." autocomplete="off" />
              
              <!-- Suggestions Dropdown with clear readable theme colors -->
              <div v-if="showKetuaSuggestions && filteredKetuaSuggestions.length > 0 && mode !== 'view'"
                class="absolute top-full left-0 right-0 mt-1 bg-[#28030B] border border-[#EBCE67]/40 rounded-lg shadow-2xl max-h-52 overflow-y-auto z-50 divide-y divide-[#EBCE67]/15">
                <div v-for="m in filteredKetuaSuggestions" :key="m.nim" @mousedown.prevent="selectKetua(m)"
                  class="px-3.5 py-2.5 hover:bg-[#50071B] cursor-pointer text-sm transition-colors flex items-center justify-between text-[#FAF4EB]">
                  <span class="font-medium text-[#FAF4EB]">{{ m.nama }}</span>
                  <span class="text-xs text-[#EBCE67] font-mono ml-2">({{ m.nim }})</span>
                </div>
              </div>
              <p v-if="!form.id_kegiatan" class="text-xs text-muted-foreground dark:text-[#FAF4EB]/60">
                Pilih kegiatan terlebih dahulu untuk mencari mahasiswa
              </p>
              <InputError :message="form.errors.nim_ketua" />
            </div>
          </div>

          <!-- Kolom Kanan: Foto Upload & Preview Compact (5 cols) -->
          <div class="md:col-span-5 p-3.5 rounded-lg border border-border/40 bg-black/5 dark:bg-[#1C040A] space-y-3">
            <Label for="foto" class="block font-medium">
              Foto Kandidat
              <span v-if="mode !== 'view'" class="text-red-500">*</span>
            </Label>
            
            <div class="flex items-center gap-4">
              <!-- Preview Box -->
              <div class="w-24 h-28 shrink-0 rounded-md border border-border/60 overflow-hidden bg-black/10 dark:bg-black/30 flex items-center justify-center">
                <img v-if="fotoPreview !== null" :src="fotoPreview" alt="Foto Preview"
                  class="w-full h-full object-cover" />
                <img v-else :src="kandidatData?.foto
                  ? `/storage/${kandidatData.foto}`
                  : '/assets/blank-profile-picture.webp'" alt="Foto"
                  class="w-full h-full object-cover" />
              </div>

              <!-- Upload Button / Info -->
              <div class="flex-1 space-y-1.5 min-w-0">
                <Input id="foto" type="file" accept="image/png, image/jpeg, image/jpg, image/webp" :tabindex="7"
                  @change="handleFotoChange" :disabled="form.processing || mode === 'view'" :readonly="mode === 'view'"
                  :class="cn(form.errors.foto ? 'border-red-500' : '', 'w-full text-xs file:text-xs')" />
                <p class="text-[11px] text-muted-foreground dark:text-[#FAF4EB]/60">
                  Format: PNG, JPG, WEBP (Maks. 2MB)
                </p>
                <InputError :message="form.errors.foto" />
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Section: Visi & Misi (Full Width) -->
        <div class="space-y-4 pt-2 border-t border-border/30">
          <!-- Visi -->
          <div class="space-y-1.5">
            <Label for="visi">
              Visi Kandidat
              <span v-if="mode !== 'view'" class="text-red-500">*</span>
            </Label>
            <Textarea id="visi" :tabindex="5" v-model="form.visi" :required="mode !== 'view'"
              :disabled="form.processing || mode === 'view'" :readonly="mode === 'view'"
              placeholder="Tuliskan visi kandidat secara jelas..."
              class="w-full min-h-[90px] text-sm leading-relaxed" />
            <InputError :message="form.errors.visi" />
          </div>

          <!-- Misi -->
          <div class="space-y-1.5">
            <Label for="misi">
              Misi Kandidat
              <span v-if="mode !== 'view'" class="text-red-500">*</span>
            </Label>
            <Textarea id="misi" :tabindex="6" v-model="form.misi" :required="mode !== 'view'"
              :disabled="form.processing || mode === 'view'" :readonly="mode === 'view'"
              placeholder="Tuliskan misi kandidat (gunakan format penomoran: 1. Misi satu 2. Misi dua...)"
              class="w-full min-h-[140px] text-sm leading-relaxed" />
            <InputError :message="form.errors.misi" />
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div v-if="mode !== 'view'" class="flex justify-end gap-2.5 mt-4 pt-3 border-t border-border/50 shrink-0">
        <Button type="button" variant="outline" @click="$emit('success')" :disabled="form.processing">
          Batal
        </Button>
        <Button type="submit" :disabled="form.processing">
          <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
          {{ mode === 'create' ? 'Tambah Kandidat' : 'Simpan Perubahan' }}
        </Button>
      </div>
    </form>
  </DialogContent>
</template>