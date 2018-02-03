<?php

namespace App\Http\Controllers;

use App\Requests\Request;
use App\Tokens\Token;
use Illuminate\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response;

class RequestController extends Controller
{

    /**
     * @var Repository
     */
    private $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param HttpRequest $req
     * @param Repository $cache
     * @return Response
     */
    public function create(HttpRequest $req, Repository $cache)
    {
        $this->guardOverQuota($req->uuid);

        /** @var Token $token */
        $token = Token::uuid($req->uuid);

        if ($token->requests()->count() >= config('app.max_requests')) {
            $this->cacheOverQuota($req->uuid);
        }

        if ($token->timeout) {
            sleep($token->timeout);
        }

        $request = Request::create([
            'token_id' => $req->uuid,
            'ip' => $req->ip(),
            'hostname' => $req->getHost(),
            'method' => $req->getMethod(),
            'user_agent' => $req->header('User-Agent'),
            'content' => file_get_contents('php://input'),
            'headers' => $req->headers->all(),
            'url' => $req->fullUrl(),
        ]);

        return new Response(
            $token->default_content,
            empty($req->statusCode) ? $token->default_status : (int)$req->statusCode,
            [
                'Content-Type' => $token->default_content_type,
                'X-Request-Id' => $request->uuid
            ]
        );
    }

    /**
     * @param $uuid
     */
    private function cacheOverQuota($uuid)
    {
        $this->cache->forever(sprintf('quota:%s', $uuid), 1);
    }

    /**
     * @param $uuid
     * @return void
     */
    private function guardOverQuota($uuid)
    {
        if ($this->cache->has(sprintf('quota:%s', $uuid))) {
            abort(Response::HTTP_GONE, 'Too many requests, please create a new URL/token');
        }
    }

    /**
     * @param string $uuid
     * @return Token
     */
    public function all($uuid)
    {
        return Token::findOrFail($uuid)->requests()->paginate(50);
    }

    /**
     * @param $tokenId
     * @param $requestId
     * @return mixed
     */
    public function find($tokenId, $requestId)
    {
        return Request::where('token_id', $tokenId)->findOrFail($requestId);
    }

    /**
     * @param string $tokenId
     * @param string $requestId
     * @return JsonResponse
     */
    public function delete($tokenId, $requestId)
    {
        return new JsonResponse([
            'status' => Request::where('token_id', $tokenId)
                ->findOrFail($requestId)
                ->delete()
        ]);
    }

}
