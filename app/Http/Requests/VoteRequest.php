<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'kandidat' => ['required', 'integer', 'exists:kandidat,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if (! $user) {
                return;
            }

            // User bukan admin dan status aktif
            if ($user->is_admin) {
                $validator->errors()->add('user', 'Sebagai admin, Anda tidak berhak melakukan voting.');
                return;
            }

            if (! $user->isActive()) {
                $validator->errors()->add('user', 'Anda harus terdaftar sebagai mahasiswa aktif untuk melakukan voting.');
                return;
            }

            $idKegiatan = $this->route('id');
            $kegiatan = Kegiatan::find($idKegiatan);

            if (! $kegiatan) {
                $validator->errors()->add('kegiatan', 'Kegiatan pemilihan tidak ditemukan.');
                return;
            }

            // Kegiatan sedang berlangsung (waktu_mulai <= sekarang <= waktu_selesai)
            if (now()->isBefore($kegiatan->waktu_mulai)) {
                $validator->errors()->add('kegiatan', 'Periode pemilihan belum dimulai.');
                return;
            }

            if (now()->isAfter($kegiatan->waktu_selesai)) {
                $validator->errors()->add('kegiatan', 'Waktu pemilihan sudah berakhir.');
                return;
            }

            // Kandidat termasuk kegiatan yang dimaksud
            $kandidatId = (int) $this->input('kandidat');
            $kandidat = $kegiatan->kandidat()->where('id', $kandidatId)->first();
            if (! $kandidat) {
                $validator->errors()->add('kandidat', 'Kandidat tidak ditemukan atau bukan bagian dari kegiatan ini.');
                return;
            }

            // Jika kegiatan berlingkup prodi, pastikan prodi sesuai
            if ($kegiatan->ruang_lingkup === 'program studi' && (int) $kegiatan->id_program_studi !== (int) $user->id_program_studi) {
                $validator->errors()->add('kegiatan', 'Kegiatan ini bukan untuk program studi Anda.');
                return;
            }
        });
    }

    public function messages(): array
    {
        return [
            'kandidat.required' => 'Pilihan kandidat wajib ada.',
            'kandidat.integer'  => 'Data kandidat tidak valid.',
            'kandidat.exists'   => 'Kandidat tidak ditemukan atau bukan bagian dari kegiatan ini.',
        ];
    }
}
