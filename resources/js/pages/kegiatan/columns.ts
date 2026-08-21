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
                class: "flex place-self-start",
            }, () => ["Nama", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => h('div', { class: 'text-left truncate' }, row.getValue('nama')),
    },
    {
        accessorKey: "tahun",
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-center",
            }, () => ["Tahun", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => {
            const tahun = row.getValue("tahun") as number;
            const colorClasses = [
                "text-center bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
                "text-center bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
                "text-center bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
                "text-center bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
            ];
            const colorClass = colorClasses[tahun % 4];
            return h("span", { class: `${colorClass} px-2 py-1 rounded-md font-medium flex place-self-center mx-auto` }, tahun);
        },
    },
    {
    accessorKey: 'waktu_mulai',
    header: () => h('div', 'Waktu Mulai'),
    cell: ({ row }) => {
      return h('div', { class: 'text-left' }, row.getValue('waktu_mulai'))
    },
  },
  {
    accessorKey: 'waktu_selesai',
    header: () => h('div', 'Waktu Selesai'),
    cell: ({ row }) => {
      return h('div', { class: 'text-left' }, row.getValue('waktu_selesai'))
    },
  },
  {
    accessorKey: 'status',
    header: () => h('div', 'Status'),
    cell: ({ row }) => {
      const status: Kegiatan['status'] = row.getValue('status')
      return h('div', { class: 'inline-block text-xs px-2 py-1.5 rounded-md font-medium ' + (status == 'Coming Soon' ? 'bg-yellow-100 text-yellow-800' : (status == 'Sedang Berlangsung' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')) }, status)
    },
  },
    {
        accessorKey: 'jumlah_pemilih',
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-center",
            }, () => ["Jumlah Pemilih", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => {
            const jumlahPemilih = row.original.jumlah_pemilih || 0;
            const totalMahasiswa = row.original.total_mahasiswa || 0;
            const percentage = totalMahasiswa > 0 ? ((jumlahPemilih / totalMahasiswa) * 100).toFixed(1) : '0.0';

            return h('div', { class: 'text-center' }, [
                h('div', { class: 'font-semibold' }, `${jumlahPemilih} / ${totalMahasiswa}`),
                h('div', { class: 'text-xs text-muted-foreground' }, `${percentage}%`)
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