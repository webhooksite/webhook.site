<?php

namespace App\Storage;

interface TokenStore
{
    /**
     * @param string $tokenId
     * @return Token
     */
    public function find($tokenId);

    /**
     * @param Token $token
     * @return int
     */
    public function countRequests(Token $token);

    /**
     * @param Token $token
     * @return Token
     */
    public function store(Token $token);

    /**
     * @param Token $token
     * @return Token
     */
    public function delete(Token $token);

}