<?php

namespace App\Events;

use App\Requests\Request;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RequestCreated implements ShouldBroadcast
{
    /**
     * @var Request
     */
    public $request;
    
    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var bool
     */
    public $truncated = false;

    /**
     * NewRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        if (mb_strlen($this->request->toJson()) > 10000) {
            unset(
                $this->request->content,
                $this->request->headers,
                $this->request->user_agent
            );
            $this->truncated = true;
        }

        $this->total = Request::where('token_id', $request->token_id)->count();
    }

    /**
     * @return string
     */
    public function broadcastAs()
    {
        return 'request.created';
    }

    /**
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel((string)$this->request->token_id);
    }
}
