<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UploadStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $uploadId;

    public function __construct($message, $uploadId)
    {
        $this->message = $message;
        $this->uploadId = $uploadId;
    }

    public function broadcastOn()
    {
        return new Channel('uploads'); // or "uploads.{$this->uploadId}" for user-specific
    }

    public function broadcastAs()
    {
        return 'upload.status.updated';
    }
}
