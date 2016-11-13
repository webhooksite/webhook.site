<?php

namespace App\Http\Controllers;


use App\Events\NewRequest;
use App\Requests\Request;
use App\Tokens\Token;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response;

class RequestController extends Controller
{

    public function create(HttpRequest $req)
    {
        $token = Token::uuid($req->uuid);

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

        $request->save();

        broadcast(new NewRequest($request));

        $statusCode = (empty($req->statusCode) ? $token->default_status : (int)$req->statusCode);

        return new Response(
            $token->default_content,
            $statusCode,
            ['Content-Type' => $token->default_content_type]
        );
    }

    public function all(HttpRequest $request, $uuid)
    {
        return Token::findOrFail($uuid)->requests()->paginate(20);
    }

}
