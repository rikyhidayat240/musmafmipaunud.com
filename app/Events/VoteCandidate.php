<?php

namespace App\Events;

use App\Http\Resources\KegiatanResource;
use App\Models\Kegiatan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCandidate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public KegiatanResource $kegiatan;
    public $id_kegiatan;
    /**
     * Create a new event instance.
     */
    public function __construct(Kegiatan $kegiatan)
    {
        $kegiatan->load('kandidat.mahasiswa');
        $this->kegiatan = new KegiatanResource($kegiatan);
        $this->id_kegiatan = $kegiatan->id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('event.'.$this->id_kegiatan),
        ];
    }
}
