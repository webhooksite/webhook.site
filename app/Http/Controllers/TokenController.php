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

        $token->save();

        return new JsonResponse($token);

    }

    /**
     * @param Request $req
     * @return JsonResponse
     */
    public function find(Request $req)
    {
        $token = Token::uuid($req->uuid);

        return new JsonResponse($token);
    }

    /**
     * @param Request $req
     * @return JsonResponse
     */
    public function delete(Request $req)
    {
        $status = Token::uuid($req->uuid)->delete();

        return new JsonResponse(['status' => $status]);
    }

}
