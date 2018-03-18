<?php

namespace App\Storage;

use Carbon\Carbon;
use Illuminate\Http\Request as HttpRequest;
use Ramsey\Uuid\Uuid;

class Request extends Entity
{
    /**
     * @param $tokenId
     * @param null $requestId
     * @return string
     */
    public static function getIdentifier($tokenId, $requestId = null)
    {
        if ($requestId) {
            return sprintf('token:%s:requests:%s', $requestId);
        }

        return sprintf('token:%s:requests', $tokenId);
    }

    /**
     * @param HttpRequest $request
     * @return Request
     */
    public static function createFromRequest(HttpRequest $request)
    {
        return new self([
            'uuid' => Uuid::uuid4()->toString(),
            'token_id' => $request->tokenId,
            'ip' => $request->ip(),
            'hostname' => $request->getHost(),
            'method' => $request->getMethod(),
            'user_agent' => $request->header('User-Agent'),
            'content' => file_get_contents('php://input'),
            'headers' => $request->headers->all(),
            'url' => $request->fullUrl(),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
    }
}