<?php

namespace App\Http\Controllers;

use App\Storage\Request;
use App\Storage\RequestStore;
use App\Storage\TokenStore;
use App\Tokens\Token;
use Carbon\Carbon;
use Illuminate\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Psy\Util\Json;
use Ramsey\Uuid\Uuid;

class RequestController extends Controller
{

    /**
     * @var Repository
     */
    private $cache;
    /**
     * @var TokenStore
     */
    private $tokens;
    /**
     * @var RequestStore
     */
    private $requests;

    /**
     * RequestController constructor.
     * @param Repository $cache
     * @param TokenStore $tokens
     * @param RequestStore $requests
     */
    public function __construct(Repository $cache, TokenStore $tokens, RequestStore $requests)
    {
        $this->cache = $cache;
        $this->tokens = $tokens;
        $this->requests = $requests;
    }

    /**
     * @param HttpRequest $httpRequest
     * @return Response
     */
    public function create(HttpRequest $httpRequest, $tokenId)
    {
        $token = $this->tokens->find($httpRequest->tokenId);

        $this->guardOverQuota($token);

        if ($token->timeout) {
            sleep($token->timeout);
        }

        $request = Request::createFromRequest($httpRequest);

        $this->requests->store($token, $request);

        return new Response(
            $token->default_content,
            $httpRequest->statusCode ?? $token->default_status,
            [
                'Content-Type' => $token->default_content_type,
                'X-Request-Id' => $request->uuid,
                'X-Token-Id' => $token->uuid,
            ]
        );
    }


    /**
     * @param Token $token
     * @return void
     */
    private function guardOverQuota($token)
    {
        if ($this->tokens->countRequests($token) >= config('app.max_requests')) {
            abort(Response::HTTP_GONE, 'Too many requests, please create a new URL/token');
        }
    }

    /**
     * @param string $tokenId
     * @param int $page
     * @param int $perPage
     * @return JsonResponse
     */
    public function all($tokenId, $page = 0, $perPage = 50)
    {
        $token = $this->tokens->find($tokenId);

        return new JsonResponse($this->requests->all($token, $page, $perPage));
    }

    /**
     * @param $tokenId
     * @param $requestId
     * @return mixed
     */
    public function find($tokenId, $requestId)
    {
        $token = $this->tokens->find($tokenId);
        $request = $this->requests->find($token, $requestId);

        return new JsonResponse($request);
    }

    /**
     * @param string $tokenId
     * @param string $requestId
     * @return JsonResponse
     */
    public function delete($tokenId, $requestId)
    {
        $token = $this->tokens->find($tokenId);
        $request = $this->requests->find($token, $requestId);

        return new JsonResponse([
            'status' => $this->requests->delete($token, $request)
        ]);
    }

}
