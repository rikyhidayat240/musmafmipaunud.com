import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface FlashMessage {
    success?: string;
    error?: string;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    alert: {title: string; message: string, type: string} | null
};

export interface ProgramStudi {
    id: number;
    kode: string;
    nama: string;
}

export interface User {
    nim: string;
    nama: string;
    email: string;
    avatar?: string;
    angkatan: number;
    is_admin: number | boolean;
    id_program_studi: number;
    email_verified_at: string | null;
    status: 'aktif' | 'nonaktif';
    created_at: string;
    updated_at: string;
    program_studi: ProgramStudi;
}

export interface Kegiatan {
    id: number;
    nama: string;
    tahun: number;
    waktu_mulai: string;
    waktu_mulai_raw: string;
    waktu_selesai: string;
    waktu_selesai_raw: string;
    status: 'Coming Soon' | 'Sedang Berlangsung' | 'Selesai';
    kandidat?: Kandidat[];
    foto: string;
    total_mahasiswa: number;
    jumlah_pemilih: number;
    ruang_lingkup: string;
}

export interface Kandidat {
    id: number;
    id_kegiatan: number;
    no_urut: string;
    foto: string;
    visi: string;
    misi: string;
    jumlah_suara: number;
    kegiatan: Kegiatan;
    mahasiswa: (User & { pivot: { jabatan: 'ketua' | 'wakil' } })[];
}

export type BreadcrumbItemType = BreadcrumbItem;
