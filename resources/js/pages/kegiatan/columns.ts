import { h } from 'vue'
import Form from './Form.vue'
import { route } from 'ziggy-js'
import { ProgramStudi, Kegiatan } from '@/types'
import { ArrowUpDown } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { ColumnDef } from '@tanstack/vue-table'
import DropdownAction from '@/components/DataTableDropdown.vue'

// Declare module untuk extend TableMeta
declare module '@tanstack/vue-table' {
    interface TableMeta<TData extends unknown> {
        helpers?: {
            [key: string]: any
        }
    }
}

// Definisi kolom untuk tabel kegiatan
export const columns: ColumnDef<Kegiatan>[] = [
    {
        accessorKey: 'foto',
        header: () => h('div', { class: 'text-center' }, 'Foto'),
        cell: ({ row }) => {
            const foto = row.getValue('foto') as string | undefined
            return h('div', { class: 'flex justify-center' }, [
                h('img', {
                    src: foto ? `/storage/${foto}` : '/assets/blank-photo-icon.jpg',
                    alt: 'Foto Kegiatan',
                    class: 'h-10 w-10 rounded-md object-cover',
                }),
            ])
        }
    },
    {
        accessorKey: 'nama',
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-start text-[#EBCE67] dark:text-[#FCCE88] hover:bg-white/10",
            }, () => ["Nama", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => h('div', { class: 'text-left truncate font-medium text-[#3B0613] dark:text-[#FAF4EB]' }, row.getValue('nama')),
    },
    {
        accessorKey: "tahun",
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-center text-[#EBCE67] dark:text-[#FCCE88] hover:bg-white/10",
            }, () => ["Tahun", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => {
            const tahun = row.getValue("tahun") as number;
            const colorClasses = [
                "text-center bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 dark:border dark:border-blue-700/40",
                "text-center bg-green-100 text-green-800 dark:bg-green-950/70 dark:text-green-300 dark:border dark:border-green-700/40",
                "text-center bg-yellow-100 text-yellow-800 dark:bg-yellow-950/70 dark:text-yellow-300 dark:border dark:border-yellow-700/40",
                "text-center bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 dark:border dark:border-red-700/40"
            ];
            const colorClass = colorClasses[tahun % 4];
            return h("span", { class: `${colorClass} px-2 py-1 rounded-md font-medium flex place-self-center mx-auto` }, tahun);
        },
    },
    {
    accessorKey: 'waktu_mulai',
    header: () => h('div', { class: 'text-left text-[#EBCE67] dark:text-[#FCCE88]' }, 'Waktu Mulai'),
    cell: ({ row }) => {
      return h('div', { class: 'text-left text-sm text-[#3B0613] dark:text-[#FAF4EB]' }, row.getValue('waktu_mulai'))
    },
  },
  {
    accessorKey: 'waktu_selesai',
    header: () => h('div', { class: 'text-left text-[#EBCE67] dark:text-[#FCCE88]' }, 'Waktu Selesai'),
    cell: ({ row }) => {
      return h('div', { class: 'text-left text-sm text-[#3B0613] dark:text-[#FAF4EB]' }, row.getValue('waktu_selesai'))
    },
  },
  {
    accessorKey: 'status',
    header: () => h('div', { class: 'text-left text-[#EBCE67] dark:text-[#FCCE88]' }, 'Status'),
    cell: ({ row }) => {
      const status: Kegiatan['status'] = row.getValue('status')
      const statusClass = status == 'Coming Soon'
        ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950/70 dark:text-yellow-300 dark:border dark:border-yellow-700/40'
        : (status == 'Sedang Berlangsung'
            ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 dark:border dark:border-blue-700/40'
            : 'bg-green-100 text-green-800 dark:bg-green-950/70 dark:text-green-300 dark:border dark:border-green-700/40');
      return h('div', { class: `inline-block text-xs px-2 py-1.5 rounded-md font-medium ${statusClass}` }, status)
    },
  },
    {
        accessorKey: 'jumlah_pemilih',
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-center text-[#EBCE67] dark:text-[#FCCE88] hover:bg-white/10",
            }, () => ["Jumlah Pemilih", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => {
            const jumlahPemilih = row.original.jumlah_pemilih || 0;
            const totalMahasiswa = row.original.total_mahasiswa || 0;
            const percentage = totalMahasiswa > 0 ? ((jumlahPemilih / totalMahasiswa) * 100).toFixed(1) : '0.0';

            return h('div', { class: 'text-center' }, [
                h('div', { class: 'font-semibold text-[#3B0613] dark:text-[#FAF4EB]' }, `${jumlahPemilih} / ${totalMahasiswa}`),
                h('div', { class: 'text-xs text-muted-foreground dark:text-[#FAF4EB]/60' }, `${percentage}%`)
            ]);
        },
    },
    {
        id: "actions",
        enableHiding: false,
        cell: ({ row, table }) => {
            const kegiatan = row.original;
            const helpers = table.options.meta?.helpers as { programStudi?: ProgramStudi[] } | undefined;

            return h(DropdownAction, {
                data: kegiatan,
                deleteRoute: route('events.destroy', kegiatan.id),
            }, {
                // Slot untuk edit form
                'edit-form': ({ data, close }: { data: Kegiatan, close: () => void }) =>
                    h(Form, {
                        mode: 'edit',
                        programStudi: helpers?.programStudi || [],
                        kegiatanData: data,
                        onSuccess: () => {
                            close();
                        }
                    })
            });
        },
    },
]