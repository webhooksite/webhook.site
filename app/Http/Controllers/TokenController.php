<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTokenRequest;
use App\Storage\Token;
use App\Storage\TokenStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TokenController extends Controller
{
    /**
     * @var TokenStore
     */
    private $tokens;

    /**
     * TokenController constructor.
     * @param TokenStore $tokens
     */
    public function __construct(TokenStore $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @param CreateTokenRequest $request
     * @return JsonResponse
     */
    public function create(CreateTokenRequest $request)
    {
        $token = Token::createFromRequest($request);

        $this->tokens->store($token);

        return new JsonResponse($token, Response::HTTP_CREATED);
    }

    /**
     * @param string $tokenId
     * @return JsonResponse
     */
    public function find($tokenId)
    {
        $token = $this->tokens->find($tokenId);

        return new JsonResponse($token);
    }

    /**
     * @param string $tokenId
     * @return JsonResponse
     */
    public function delete($tokenId)
    {
        $token = $this->tokens->find($tokenId);

        return new JsonResponse([
            'status' => (bool)$this->tokens->delete($token)
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param CreateTokenRequest $request
     * @param string $tokenId
     * @return JsonResponse
     */
    public function update(CreateTokenRequest $request, string $tokenId) : JsonResponse
    {
        $token = $this->tokens->find($tokenId);

        $token->default_content = $request->get('default_content', '');
        $token->default_status = (int)$request->get('default_status', 200);
        $token->default_content_type = $request->get('default_content_type', 'text/plain');
        $token->timeout = (int)$request->get('timeout', null);

        $this->tokens->store($token);

        return new JsonResponse($token);
    }

    public function toggleCors(string $tokenId): JsonResponse
    {
        $token = $this->tokens->find($tokenId);

        $token->cors = isset($token->cors) ? !$token->cors : true;

        $this->tokens->store($token);

        logger()->info("[CORS] $tokenId toggle");

        return new JsonResponse(['enabled' => $token->cors]);
    }
}
