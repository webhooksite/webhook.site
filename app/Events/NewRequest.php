<?php

namespace App\Events;


use App\Requests\Request;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewRequest implements ShouldBroadcast
{
    use SerializesModels;

    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function broadcastAs()
    {
        return 'request.new';
    }

    public function broadcastOn()
    {
        return new Channel((string) $this->request->token_id);
    }

}