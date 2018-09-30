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
     * @param HttpRequest $httpRequest
     * @return Request
     */
    public static function createFromRequest(HttpRequest $httpRequest)
    {
        $request = new self([
            'uuid' => Uuid::uuid4()->toString(),
            'token_id' => $httpRequest->tokenId,
            'ip' => $httpRequest->ip(),
            'hostname' => $httpRequest->getHost(),
            'method' => $httpRequest->getMethod(),
            'user_agent' => $httpRequest->header('User-Agent'),
            'content' => file_get_contents('php://input'),
            'query' => empty($httpRequest->query->all()) ? null : $httpRequest->query->all(),
            'headers' => $httpRequest->headers->all(),
            'url' => $httpRequest->fullUrl(),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        if (!$httpRequest->isJson()) {
            $request->request = empty($httpRequest->request->all()) ? null : $httpRequest->request->all();
        }

        return $request;
    }

    /**
     * @return bool
     */
    public function isJson()
    {
        return $this->headers['content-type'][0] === 'application/json';
    }
}