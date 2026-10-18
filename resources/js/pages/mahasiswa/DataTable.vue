<script setup lang="ts">
import Form from './Form.vue'
import { route } from 'ziggy-js'
import { cn } from '@/lib/utils'
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import { Dialog, DialogContent, DialogTrigger, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button, buttonVariants } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import type { ColumnDef, SortingState, ColumnFiltersState, PaginationState } from '@tanstack/vue-table'
import { CircleX, Filter, Plus, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, RefreshCw, LoaderCircle, Mail, Send } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getSortedRowModel, getFilteredRowModel, getPaginationRowModel, useVueTable } from '@tanstack/vue-table'

// Define props untuk data mahasiswa
const props = defineProps<{
    columns: ColumnDef<any, any>[]
    data: any[]
    helpers?: {
        [key: string]: any[]
    }
}>();

// State management
const sorting = ref<SortingState>([])
const columnFiltersState = ref<ColumnFiltersState>([])
const globalFilter = ref('')
const isDialogOpen = ref(false)
const isSyncDialogOpen = ref(false)
const isViewDialogOpen = ref(false)
const isBulkDialogOpen = ref(false)
const selectedRowData = ref<any>(null)
const pagination = ref<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
})

// State untuk pengiriman credentials
const isSyncing = ref(false);
const sendingNim = ref<string | null>(null);
const isBulkSending = ref(false);
const bulkOnlyUnsent = ref(true);

// Computed values
const activeFilters = computed(() => {
    return columnFiltersState.value.filter(filter => filter.value !== undefined && filter.value !== '')
})

const totalWithEmail = computed(() => {
    return props.data.filter((u: any) => u.email && !u.is_admin).length;
})

const totalUnsent = computed(() => {
    return props.data.filter((u: any) => u.email && !u.credentials_sent_at && !u.is_admin).length;
})

// Setup table instance
const table = useVueTable({
    get data() { return props.data },
    get columns() { return props.columns },
    getRowId: (row) => String(row.nim),
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    meta: {
        helpers: props.helpers
    },
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFiltersState.value },
        get globalFilter() { return globalFilter.value },
        get pagination() { return pagination.value },
    },
    onSortingChange: (updaterOrValue) => {
        sorting.value = typeof updaterOrValue === 'function'
            ? updaterOrValue(sorting.value)
            : updaterOrValue
    },
    onColumnFiltersChange: (updaterOrValue) => {
        columnFiltersState.value = typeof updaterOrValue === 'function'
            ? updaterOrValue(columnFiltersState.value)
            : updaterOrValue
    },
    onGlobalFilterChange: (updaterOrValue) => {
        globalFilter.value = typeof updaterOrValue === 'function'
            ? updaterOrValue(globalFilter.value)
            : updaterOrValue
    },
    onPaginationChange: (updaterOrValue) => {
        pagination.value = typeof updaterOrValue === 'function'
            ? updaterOrValue(pagination.value)
            : updaterOrValue
    },
})

// Helper functions
const getFilterDisplayValue = (value: any, columnId?: string): string => {
    if (Array.isArray(value)) {
        return `${value.length} dipilih`
    }

    if (columnId === 'id_program_studi') {
        const prodiName = props.helpers?.programStudi?.find(ps => ps.id === Number(value))?.nama
        return prodiName || String(value)
    }

    if (columnId === 'status') {
        if (value === 'aktif') return 'Aktif'
        if (value === 'nonaktif') return 'Nonaktif'
    }

    if (columnId === 'email_verified_at') {
        if (value === 'terverifikasi') return 'Terverifikasi'
        if (value === 'belum_terverifikasi') return 'Belum Terverifikasi'
    }

    return String(value).replace(/_/g, ' ')
}

// Column filters configuration
const columnFiltersConfig = [
    {
        columnId: 'id_program_studi',
        label: 'Prodi',
        type: 'select' as const,
    },
    {
        columnId: 'angkatan',
        label: 'Angkatan',
        type: 'select' as const,
    },
    {
        columnId: 'status',
        label: 'Status',
        type: 'select' as const,
    },
    {
        columnId: 'email_verified_at',
        label: 'Verifikasi',
        type: 'select' as const,
    },
]

const getUniqueValues = (columnId: string): string[] => {
    const uniqueValues = new Set<string>()
    props.data.forEach((row: any) => {
        const value = row[columnId]
        if (value != null) {
            uniqueValues.add(String(value))
        }
    })
    return Array.from(uniqueValues).sort()
}

const getStatusOptions = () => {
    return [
        { value: 'terverifikasi', label: 'Sudah' },
        { value: 'belum_terverifikasi', label: 'Belum' }
    ]
}

const handleRowClick = (rowData: any) => {
    selectedRowData.value = rowData
    isViewDialogOpen.value = true
}

// Function untuk sync mahasiswa
const handleSyncMahasiswa = () => {
    const currentYear = new Date().getFullYear();
    
    isSyncing.value = true;
    router.post(
        route('users.sync-data', currentYear),
        {},
        {
            onSuccess: () => {
                isSyncDialogOpen.value = false;
                isSyncing.value = false;
            },
            onError: () => {
                isSyncing.value = false;
            },
            onFinish: () => {
                isSyncing.value = false;
            }
        }
    );
};

// Kirim password per mahasiswa (satuan)
const handleSendCredentials = (nim: string, event: Event) => {
    event.stopPropagation();
    if (!confirm('Kirim password baru via email ke mahasiswa ini? Password lama akan direset.')) return;
    
    sendingNim.value = nim;
    router.post(
        route('users.send-credentials', nim),
        {},
        {
            onFinish: () => {
                sendingNim.value = null;
            }
        }
    );
};

// Kirim password massal (bulk)
const handleBulkSend = () => {
    isBulkSending.value = true;
    router.post(
        route('users.send-bulk-credentials'),
        { only_unsent: bulkOnlyUnsent.value },
        {
            onSuccess: () => {
                isBulkDialogOpen.value = false;
                isBulkSending.value = false;
            },
            onError: () => {
                isBulkSending.value = false;
            },
            onFinish: () => {
                isBulkSending.value = false;
            }
        }
    );
};

const handleSuccess = () => {
    isDialogOpen.value = false
}

watch(isViewDialogOpen, (newValue) => {
    if (!newValue) {
        selectedRowData.value = null
    }
})
</script>

<template>
    <div class="flex flex-col w-full gap-4">
        <!-- Header -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1 mb-2 md:mb-4">
                <h2 class="musma-section-title text-lg md:text-xl text-[#3B0613] dark:text-[#FCCE88]">Daftar Mahasiswa</h2>
                <p class="text-xs text-muted-foreground dark:text-[#FAF4EB]/60">Kelola akun dan distribusi password peserta MUSMA</p>
            </div>

            <div class="flex flex-wrap items-center justify-end w-full md:w-auto gap-2">
                <!-- Search bar -->
                <div class="flex items-center">
                    <Input placeholder="Cari mahasiswa..." :model-value="globalFilter"
                        @update:model-value="table.setGlobalFilter($event)"
                        class="text-sm w-44 sm:w-56 bg-white dark:bg-[#1E040B] border-[#C8B8BB] dark:border-[rgba(252,206,136,0.25)] text-foreground dark:text-[#FAF4EB] placeholder:text-muted-foreground/60 dark:placeholder:text-[#FAF4EB]/40 focus-visible:ring-[#FCCE88]" />
                </div>

                <!-- Data filter -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm" class="h-9 border-[#C8B8BB] dark:border-[rgba(252,206,136,0.25)] bg-white dark:bg-[#1E040B] text-foreground dark:text-[#FAF4EB] hover:bg-[#FAF4ED] dark:hover:bg-[#350612]">
                            <Filter class="size-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-[200px]">
                        <div class="flex flex-col gap-3 p-2">
                            <!-- Prodi Filter -->
                            <div class="flex flex-col gap-2">
                                <Select :model-value="String(table.getColumn('id_program_studi')?.getFilterValue())"
                                    @update:model-value="(value) => table.getColumn('id_program_studi')?.setFilterValue(value === 'all' ? undefined : value)">
                                    <SelectTrigger class="text-sm capitalize text-foreground">
                                        <SelectValue placeholder="Pilih Prodi" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all" class="text-foreground">Semua</SelectItem>
                                        <SelectItem v-for="value in getUniqueValues('id_program_studi')" :key="value"
                                            :value="value" class="capitalize text-foreground">
                                            {{props.helpers?.programStudi?.find(ps => ps.id === Number(value))?.nama || value}}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Angkatan Filter -->
                            <div class="flex flex-col gap-2">
                                <Select :model-value="String(table.getColumn('angkatan')?.getFilterValue())"
                                    @update:model-value="(value) => table.getColumn('angkatan')?.setFilterValue(value === 'all' ? undefined : value)">
                                    <SelectTrigger class="text-sm capitalize text-foreground">
                                        <SelectValue placeholder="Pilih Angkatan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua</SelectItem>
                                        <SelectItem v-for="value in getUniqueValues('angkatan')" :key="value"
                                            :value="value" class="capitalize">
                                            {{ value }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Status Filter -->
                            <div class="flex flex-col gap-2">
                                <Select :model-value="String(table.getColumn('status')?.getFilterValue())"
                                    @update:model-value="(value) => table.getColumn('status')?.setFilterValue(value === 'all' ? undefined : value)">
                                    <SelectTrigger class="text-sm capitalize text-foreground">
                                        <SelectValue placeholder="Pilih Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua</SelectItem>
                                        <SelectItem v-for="value in getUniqueValues('status')" :key="value"
                                            :value="value" class="capitalize">
                                            {{ value }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Verifikasi Filter -->
                            <div class="flex flex-col gap-2">
                                <Select :model-value="String(table.getColumn('email_verified_at')?.getFilterValue())"
                                    @update:model-value="(value) => table.getColumn('email_verified_at')?.setFilterValue(value === 'all' ? undefined : value)">
                                    <SelectTrigger class="text-sm capitalize text-foreground">
                                        <SelectValue placeholder="Pilih Verifikasi" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua</SelectItem>
                                        <SelectItem v-for="option in getStatusOptions()" :key="option.value"
                                            :value="option.value" class="capitalize">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Bulk Send Credentials button -->
                <Dialog v-model:open="isBulkDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="outline" size="sm" class="h-9 gap-1.5 border-amber-600/40 text-amber-700 dark:text-amber-300 bg-white dark:bg-[#1E040B] hover:bg-amber-500/10" title="Kirim password ke email">
                            <Mail class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            <span class="text-xs font-semibold">Kirim Massal</span>
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-lg">
                        <DialogHeader>
                            <DialogTitle class="flex items-center gap-2">
                                <Mail class="h-5 w-5 text-amber-600" />
                                Kirim Password ke Semua Peserta
                            </DialogTitle>
                            <DialogDescription class="pt-2 space-y-3">
                                <p>
                                    Sistem akan membuatkan password baru secara otomatis dan mengirimkannya langsung ke email masing-masing peserta MUSMA.
                                </p>
                                <div class="p-3 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs text-foreground space-y-1">
                                    <p><strong>Total Peserta dengan email:</strong> {{ totalWithEmail }} orang</p>
                                    <p><strong>Belum pernah dikirimi email:</strong> {{ totalUnsent }} orang</p>
                                </div>
                                <div class="pt-2 flex items-center gap-2">
                                    <input 
                                        type="checkbox" 
                                        id="bulkOnlyUnsent" 
                                        v-model="bulkOnlyUnsent"
                                        class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                    />
                                    <label for="bulkOnlyUnsent" class="text-xs text-foreground font-medium cursor-pointer">
                                        Hanya kirim ke yang belum pernah menerima email ({{ totalUnsent }} orang)
                                    </label>
                                </div>
                            </DialogDescription>
                        </DialogHeader>

                        <div class="flex justify-end gap-2 pt-4">
                            <Button 
                                variant="outline" 
                                @click="isBulkDialogOpen = false" 
                                :disabled="isBulkSending"
                            >
                                Batal
                            </Button>
                            <Button 
                                @click="handleBulkSend" 
                                :disabled="isBulkSending || (bulkOnlyUnsent ? totalUnsent === 0 : totalWithEmail === 0)"
                                class="gap-1.5"
                                style="background-color:#51071B; color:#EBCE67;"
                            >
                                <LoaderCircle v-if="isBulkSending" class="h-4 w-4 animate-spin" />
                                <Send v-else class="h-4 w-4" />
                                Kirim Sekarang
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>

                <!-- Sync Data Mahasiswa button -->
                <Dialog v-model:open="isSyncDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="outline" size="sm" class="h-9 border-[#C8B8BB] dark:border-[rgba(252,206,136,0.25)] bg-white dark:bg-[#1E040B] text-foreground dark:text-[#FAF4EB] hover:bg-[#FAF4ED] dark:hover:bg-[#350612]" title="Sinkronkan dari Excel">
                            <RefreshCw class="h-4 w-4" />
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-xl">
                        <DialogHeader>
                            <DialogTitle>Sinkronisasi Data Mahasiswa</DialogTitle>
                            <DialogDescription>
                                Apakah Anda yakin ingin menyinkronkan data mahasiswa tahun {{ new Date().getFullYear() }} dari Excel? 
                                Proses ini akan memperbarui atau menambahkan data mahasiswa yang ada.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="flex justify-end gap-2 pt-4">
                            <Button 
                                variant="outline" 
                                @click="isSyncDialogOpen = false" 
                                :disabled="isSyncing"
                            >
                                Batal
                            </Button>
                            <Button 
                                @click="handleSyncMahasiswa" 
                                :disabled="isSyncing"
                            >
                                <LoaderCircle v-if="isSyncing" class="h-4 w-4 animate-spin" />
                                Sinkronkan
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>

                <!-- Create button -->
                <Dialog v-model:open="isDialogOpen">
                    <DialogTrigger :class="cn(buttonVariants({ variant: 'default', size: 'sm' }), 'h-9 bg-[#51071B] dark:bg-[#FCCE88] text-[#EBCE67] dark:text-[#2A050E] hover:bg-[#3B0613] dark:hover:bg-[#FCCE88]/90 font-semibold shadow-sm')">
                        <Plus class="size-4" />
                        Tambah
                    </DialogTrigger>
                    <Form mode="create" :program-studi="helpers!.programStudi" @success="handleSuccess" />
                </Dialog>
            </div>
        </div>

        <!-- Data Table -->
        <div class="rounded-xl overflow-hidden bg-white dark:bg-[#1C040A] border border-[#C8B8BB] dark:border-[rgba(252,206,136,0.2)] shadow-sm dark:shadow-[0_4px_20px_rgba(0,0,0,0.5)]">
            <Table>
                <!-- Active Filters -->
                <TableHeader v-if="globalFilter || activeFilters.length > 0">
                    <TableRow class="bg-[#F8F2EA] dark:bg-[#23050D] border-b border-[#E3D7D9] dark:border-[rgba(252,206,136,0.15)]">
                        <TableCell :colspan="columns.length + 1">
                            <div class="flex items-center gap-2 py-1 px-4 flex-wrap">
                                <span class="text-sm font-medium text-[#3B0613] dark:text-[#FAF4EB]">Filter aktif:</span>

                                <!-- Global Filter Badge -->
                                <Button v-if="globalFilter" variant="secondary" size="sm" class="h-6 px-2 bg-[#E3D7D9] dark:bg-[#3D0B18] text-[#3B0613] dark:text-[#FAF4EB] border border-transparent dark:border-[rgba(252,206,136,0.2)]"
                                    @click="table.setGlobalFilter('')">
                                    Pencarian: {{ globalFilter }}
                                    <CircleX class="ml-2 size-4" />
                                </Button>

                                <!-- Column Filters Badges -->
                                <template v-for="filter in activeFilters" :key="filter.id">
                                    <Button v-if="columnFiltersConfig.find(c => c.columnId === filter.id)"
                                        variant="secondary" size="sm" class="h-6 px-2 bg-[#E3D7D9] dark:bg-[#3D0B18] text-[#3B0613] dark:text-[#FAF4EB] border border-transparent dark:border-[rgba(252,206,136,0.2)]"
                                        @click="table.getColumn(filter.id)?.setFilterValue(undefined)">
                                        <span class="capitalize">
                                            {{columnFiltersConfig.find(c => c.columnId === filter.id)?.label}}:
                                            {{ getFilterDisplayValue(filter.value, filter.id) }}
                                        </span>
                                        <CircleX class="size-4" />
                                    </Button>
                                </template>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableHeader>

                <!-- Table Header -->
                <TableHeader v-if="table.getRowModel().rows?.length">
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id"
                        class="bg-[#51071B] dark:bg-[#350612] border-b border-[#3B0613] dark:border-[rgba(252,206,136,0.2)]">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" class="py-3 text-[#EBCE67] dark:text-[#FCCE88] font-bold text-[0.8rem] tracking-wider uppercase [&_button]:text-[#EBCE67] dark:[&_button]:text-[#FCCE88] [&_button]:hover:bg-white/10">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                :props="header.getContext()" />
                        </TableHead>
                        <TableHead class="py-3 text-right pr-4 text-[#EBCE67] dark:text-[#FCCE88] font-bold text-[0.8rem] tracking-wider uppercase">
                            Email
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <!-- Table Body -->
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                            :data-state="row.getIsSelected() ? 'selected' : undefined" class="cursor-pointer"
                            @click="() => handleRowClick(row.original)">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="truncate max-w-96">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>

                            <!-- Kirim Credentials per row button -->
                            <TableCell class="w-20 text-right pr-4" @click.stop>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 px-2 text-xs gap-1"
                                    :class="row.original.credentials_sent_at ? 'text-green-600 dark:text-green-400 hover:text-green-500' : 'text-amber-600 dark:text-amber-400 hover:text-amber-500'"
                                    :disabled="sendingNim === row.original.nim || !row.original.email"
                                    :title="!row.original.email ? 'Belum ada email' : (row.original.credentials_sent_at ? 'Email sudah terkirim — klik untuk kirim ulang' : 'Kirim password ke email')"
                                    @click="handleSendCredentials(row.original.nim, $event)"
                                >
                                    <LoaderCircle v-if="sendingNim === row.original.nim" class="h-3 w-3 animate-spin" />
                                    <Mail v-else class="h-3 w-3" />
                                    <span class="text-[11px]">{{ row.original.credentials_sent_at ? 'Terkirim' : 'Kirim' }}</span>
                                </Button>
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length + 1" class="h-36 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <CircleX class="size-8 text-primary/70" />
                                    <p class="text-muted-foreground dark:text-[#FAF4EB]/70">Tidak ada data yang ditemukan.</p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div v-if="table.getRowCount() > 0" class="flex items-center mt-2 justify-between px-2">
            <div class="text-muted-foreground dark:text-[#FAF4EB]/70 text-sm">
                {{ table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1 }} -
                {{
                    Math.min(
                        table.getState().pagination.pageSize * (table.getState().pagination.pageIndex + 1),
                        table.getFilteredRowModel().rows.length
                    )
                }} dari {{ table.getFilteredRowModel().rows.length }} baris.
            </div>

            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium hidden sm:flex text-[#3B0613] dark:text-[#FAF4EB]">Per halaman</p>
                <Select :model-value="`${table.getState().pagination.pageSize}`"
                    @update:model-value="(value) => table.setPageSize(Number(value))">
                    <SelectTrigger class="h-8 w-[70px] bg-white dark:bg-[#1E040B] border-[#C8B8BB] dark:border-[rgba(252,206,136,0.25)] text-foreground dark:text-[#FAF4EB]">
                        <SelectValue :placeholder="`${table.getState().pagination.pageSize}`" />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem v-for="pageSize in [5, 10, 25, 50, 100]" :key="pageSize" :value="`${pageSize}`">
                            {{ pageSize }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="flex items-center space-x-4 lg:space-x-6">
                <div class="hidden w-auto md:flex md:items-center md:justify-center text-sm font-medium text-[#3B0613] dark:text-[#FAF4EB]">
                    Halaman {{ table.getState().pagination.pageIndex + 1 }} dari
                    {{ table.getPageCount() }}
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" class="hidden w-8 h-8 p-0 lg:flex border-[#C8B8BB] dark:border-[rgba(252,206,136,0.25)] bg-white dark:bg-[#1E040B] text-[#3B0613] dark:text-[#FAF4EB] hover:bg-[#FAF4ED] dark:hover:bg-[#350612] disabled:opacity-40" :disabled="!table.getCanPreviousPage()"
                        @click="table.setPageIndex(0)">
                        <span class="sr-only">Ke halaman pertama</span>
                        <ChevronsLeft class="w-4 h-4" />
                    </Button>
                    <Button variant="outline" class="w-8 h-8 p-0 border-[#C8B8BB] dark:border-[rgba(252,206,136,0.25)] bg-white dark:bg-[#1E040B] text-[#3B0613] dark:text-[#FAF4EB] hover:bg-[#FAF4ED] dark:hover:bg-[#350612] disabled:opacity-40" :disabled="!table.getCanPreviousPage()"
                        @click="table.previousPage()">
                        <span class="sr-only">Ke halaman sebelumnya</span>
                        <ChevronLeft class="w-4 h-4" />
                    </Button>
                    <Button variant="outline" class="w-8 h-8 p-0 border-[#C8B8BB] dark:border-[rgba(252,206,136,0.25)] bg-white dark:bg-[#1E040B] text-[#3B0613] dark:text-[#FAF4EB] hover:bg-[#FAF4ED] dark:hover:bg-[#350612] disabled:opacity-40" :disabled="!table.getCanNextPage()"
                        @click="table.nextPage()">
                        <span class="sr-only">Ke halaman selanjutnya</span>
                        <ChevronRight class="w-4 h-4" />
                    </Button>
                    <Button variant="outline" class="hidden w-8 h-8 p-0 lg:flex border-[#C8B8BB] dark:border-[rgba(252,206,136,0.25)] bg-white dark:bg-[#1E040B] text-[#3B0613] dark:text-[#FAF4EB] hover:bg-[#FAF4ED] dark:hover:bg-[#350612] disabled:opacity-40" :disabled="!table.getCanNextPage()"
                        @click="table.setPageIndex(table.getPageCount() - 1)">
                        <span class="sr-only">Ke halaman terakhir</span>
                        <ChevronsRight class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- View Dialog -->
        <Dialog v-model:open="isViewDialogOpen">
            <Form v-if="selectedRowData" mode="view" :mahasiswa-data="selectedRowData"
                :program-studi="helpers!.programStudi" @close="isViewDialogOpen = false" />
        </Dialog>
    </div>
</template>