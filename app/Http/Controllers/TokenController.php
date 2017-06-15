<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Tokens\Token;

class TokenController extends Controller
{

    /**
     * @param CreateTokenRequest $req
     * @return JsonResponse
     */
    public function create(CreateTokenRequest $req)
    {
        $token = Token::create([
            'ip' => $req->ip(),
            'user_agent' => $req->header('User-Agent'),
            'default_content' => $req->get('default_content', ''),
            'default_status' => $req->get('default_status', 200),
            'default_content_type' => $req->get('default_content_type', 'text/plain'),
            'timeout' => $req->get('timeout', null),
        ]);

        return new JsonResponse($token);

    }

    /**
     * @param string $uuid
     * @return JsonResponse
     */
    public function find($uuid)
    {
        return new JsonResponse(Token::uuid($uuid));
    }

    /**
     * @param string $uuid
     * @return JsonResponse
     */
    public function delete($uuid)
    {
        return new JsonResponse([
            'status' => Token::uuid($uuid)->delete()
        ]);
    }

}
