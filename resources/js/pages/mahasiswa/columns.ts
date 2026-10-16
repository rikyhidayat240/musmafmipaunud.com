import { h } from 'vue'
import Form from './Form.vue'
import { route } from 'ziggy-js'
import { ProgramStudi, User } from '@/types'
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

// Definisi kolom untuk tabel mahasiswa
export const columns: ColumnDef<User>[] = [
    {
        accessorKey: 'avatar',
        header: () => h('div', { class: 'text-center' }, 'Profil'),
        cell: ({ row }) => {
            const avatar = row.getValue('avatar') as string | undefined
            return h('div', { class: 'flex justify-center' }, [
                h('img', {
                    src: avatar ? `/storage/${avatar}` : '/assets/blank-profile-picture.webp',
                    alt: 'Profil',
                    class: 'h-10 w-10 rounded-md object-cover',
                }),
            ])
        }
    },
    {
        accessorKey: 'nim',
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-center text-[#EBCE67] dark:text-[#FCCE88] hover:bg-white/10",
            }, () => ["NIM", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => h('div', { class: 'text-center font-mono text-sm text-[#3B0613] dark:text-[#FAF4EB]' }, row.getValue('nim')),
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
        accessorKey: 'id_program_studi',
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-center text-[#EBCE67] dark:text-[#FCCE88] hover:bg-white/10",
            }, () => ["Prodi", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row, table }) => {
            const idProgramStudi = row.getValue('id_program_studi') as number;
            const helpers = table.options.meta?.helpers as { programStudi?: ProgramStudi[] } | undefined;
            const programStudiName = helpers?.programStudi?.find(ps => ps.id === idProgramStudi)?.nama || 'N/A';

            const colorMap: { [key: number]: string } = {
                1: 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300 dark:border dark:border-purple-700/40',
                2: 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 dark:border dark:border-blue-700/40',
                3: 'bg-green-100 text-green-800 dark:bg-green-950/70 dark:text-green-300 dark:border dark:border-green-700/40',
                4: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950/70 dark:text-yellow-300 dark:border dark:border-yellow-700/40',
                5: 'bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 dark:border dark:border-red-700/40',
                6: 'bg-gray-100 text-gray-800 dark:bg-zinc-800 dark:text-zinc-200 dark:border dark:border-zinc-700/40'
            };

            const colorClass = colorMap[idProgramStudi] || 'bg-gray-100 text-gray-800 dark:bg-zinc-800 dark:text-zinc-200';

            return h('span', { class: `${colorClass} px-2 py-1 rounded-md font-medium flex place-self-center mx-auto text-xs` }, programStudiName);
        },
        filterFn: (row, id, value) => {
            return String(row.getValue(id)) === String(value);
        },
    },
    {
        accessorKey: "email",
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-start text-[#EBCE67] dark:text-[#FCCE88] hover:bg-white/10",
            }, () => ["Email", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => h("div", { class: "text-left truncate text-sm text-[#3B0613] dark:text-[#FAF4EB]" }, row.getValue("email") ?? "Belum terdaftar"),
    },
    {
        accessorKey: "angkatan",
        header: ({ column }) => {
            return h(Button, {
                variant: "ghost",
                onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
                class: "flex place-self-center text-[#EBCE67] dark:text-[#FCCE88] hover:bg-white/10",
            }, () => ["Akt", h(ArrowUpDown, { class: "h-4 w-4" })])
        },
        cell: ({ row }) => {
            const angkatan = row.getValue("angkatan") as number;
            const colorClasses = [
                "text-center bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 dark:border dark:border-blue-700/40",
                "text-center bg-green-100 text-green-800 dark:bg-green-950/70 dark:text-green-300 dark:border dark:border-green-700/40",
                "text-center bg-yellow-100 text-yellow-800 dark:bg-yellow-950/70 dark:text-yellow-300 dark:border dark:border-yellow-700/40",
                "text-center bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 dark:border dark:border-red-700/40"
            ];
            const colorClass = colorClasses[angkatan % 4];
            return h("span", { class: `${colorClass} px-2 py-1 rounded-md font-medium flex place-self-center mx-auto text-xs` }, angkatan);
        },
    },
    {
        accessorKey: 'status',
        header: () => h('div', { class: 'text-center text-[#EBCE67] dark:text-[#FCCE88]' }, 'Status'),
        cell: ({ row }) => {
            const status = row.getValue('status') as string
            if (status === 'aktif') {
                return h('span', { class: 'px-2 py-1 bg-green-100 text-green-800 dark:bg-green-950/70 dark:text-green-300 dark:border dark:border-green-700/40 rounded-md font-medium flex place-self-center mx-auto text-xs' }, 'Aktif')
            } else {
                return h('span', { class: 'px-2 py-1 bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 dark:border dark:border-red-700/40 rounded-md font-medium flex place-self-center mx-auto text-xs' }, 'Nonaktif')
            }
        },
        filterFn: (row, id, value) => {
            return String(row.getValue(id)) === String(value);
        },
    },
    {
        accessorKey: 'email_verified_at',
        header: () => h('div', { class: 'text-center text-[#EBCE67] dark:text-[#FCCE88]' }, 'Verifikasi'),
        cell: ({ row }) => {
            const verifiedAt = row.getValue('email_verified_at') as string | null
            if (verifiedAt) {
                return h('span', { class: 'px-2 py-1 bg-green-100 text-green-800 dark:bg-green-950/70 dark:text-green-300 dark:border dark:border-green-700/40 rounded-md font-medium flex place-self-center text-xs' }, 'Sudah')
            } else {
                return h('span', { class: 'px-2 py-1 bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 dark:border dark:border-red-700/40 rounded-md font-medium flex place-self-center text-xs' }, 'Belum')
            }
        },
        filterFn: (row, id, value) => {
            const verifiedAt = row.getValue(id) as string | null;
            if (value === 'terverifikasi') {
                return verifiedAt !== null;
            } else if (value === 'belum_terverifikasi') {
                return verifiedAt === null;
            }
            return true;
        },
    },
    {
        id: "actions",
        enableHiding: false,
        cell: ({ row, table }) => {
            const mahasiswa = row.original;
            const helpers = table.options.meta?.helpers as { programStudi?: ProgramStudi[] } | undefined;

            return h(DropdownAction, {
                data: mahasiswa,
                deleteRoute: route('users.destroy', mahasiswa.nim),
            }, {
                // Slot untuk edit form
                'edit-form': ({ data, close }: { data: User, close: () => void }) => 
                    h(Form, {
                        mode: 'edit',
                        programStudi: helpers?.programStudi || [],
                        mahasiswaData: data,
                        onSuccess: () => {
                            close();
                        }
                    })
            });
        },
    },
]