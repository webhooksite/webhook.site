<?php

namespace App\Http\Controllers;

use App\Events\RequestCreated;
use App\Requests\Request;
use App\Tokens\Token;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response;

class RequestController extends Controller
{

    /**
     * @param HttpRequest $req
     * @return Response
     */
    public function create(HttpRequest $req)
    {
        $token = Token::uuid($req->uuid);

        if ($token->requests()->count() >= config('app.max_requests')) {
            return new Response('Too many requests, please create a new URL/token.', Response::HTTP_GONE);
        }

        if ($token->timeout) {
            sleep($token->timeout);
        }

        Request::create([
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
            ['Content-Type' => $token->default_content_type]
        );
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
