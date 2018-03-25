<?php

namespace App\Events;

use App\Storage\Request;
use App\Storage\Token;
use App\Storage\TokenStore;
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
     * @param Token $token
     * @param Request $request
     */
    public function __construct(Token $token, Request $request)
    {
        $this->request = $request;

        if (mb_strlen($this->request->toJson()) > 1000 * 1000) {
            unset(
                $this->request->content,
                $this->request->headers,
                $this->request->user_agent
            );
            $this->truncated = true;
        }

        $this->total = app(TokenStore::class)->countRequests($token);
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
