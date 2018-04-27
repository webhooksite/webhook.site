<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTokenRequest;
use App\Storage\Token;
use App\Storage\TokenStore;
use Illuminate\Http\JsonResponse;

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

        return new JsonResponse($token);
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
        ]);
    }
}
