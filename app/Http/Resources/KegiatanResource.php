<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KegiatanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'tahun' => $this->tahun,
            'foto' => $this->foto,
            'waktu_mulai' => $this->waktu_mulai->locale('id_ID')->isoFormat('DD MMMM YYYY, HH:mm'),
            'waktu_mulai_raw' => $this->waktu_mulai->format('Y-m-d H:i:s'),
            'waktu_selesai' => $this->waktu_selesai->locale('id_ID')->isoFormat('DD MMMM YYYY, HH:mm'),
            'waktu_selesai_raw' => $this->waktu_selesai->format('Y-m-d H:i:s'),
            'total_mahasiswa' => $this->total_mahasiswa,
            'jumlah_pemilih' => $this->jumlah_pemilih,
            'kandidat' => KandidatResource::collection($this->whenLoaded('kandidat')),
            'status' => now()->betweenIncluded($this->waktu_mulai, $this->waktu_selesai) ? 'Sedang Berlangsung' : (now()->isBefore($this->waktu_mulai) ? 'Coming Soon' : 'Selesai')
        ];
    }
}
