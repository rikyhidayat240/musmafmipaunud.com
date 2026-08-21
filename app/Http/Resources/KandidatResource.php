<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KandidatResource extends JsonResource
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
            'no_urut' => $this->no_urut,
            'foto' => $this->foto,
            'visi' => $this->visi,
            'misi' => $this->misi,
            'jumlah_suara' => $this->jumlah_suara,
            'kegiatan' => new KegiatanResource($this->whenLoaded('kegiatan')),
            'mahasiswa' => MahasiswaResource::collection($this->whenLoaded('mahasiswa')),
        ];
    }
}
