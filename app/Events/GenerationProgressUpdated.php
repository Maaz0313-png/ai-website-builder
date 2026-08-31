<?php

namespace App\Events;

use App\Models\Generation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GenerationProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Generation $generation,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('generations.'.$this->generation->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'generation.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->generation->id,
            'project_id' => $this->generation->project_id,
            'status' => $this->generation->status,
            'progress' => $this->generation->progress,
            'error' => $this->generation->error,
        ];
    }
}
