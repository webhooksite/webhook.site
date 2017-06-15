<?php

namespace App\Requests;

use App\Events\RequestCreated;

class RequestObserver
{
    /**
     * @param Request $request
     * @return void
     */
    public function created(Request $request)
    {
        broadcast(new RequestCreated($request));
    }
}