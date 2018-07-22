<?php

namespace App\Storage;

use Illuminate\Support\Collection;

interface RequestStore
{
    /**
     * @param Token $token
     * @param string $requestId
     * @return Request
     */
    public function find(Token $token, $requestId);

    /**
     * @param Token $token
     * @param int $page
     * @param int $perPage
     * @return Collection
     */
    public function all(Token $token, $page = 0, $perPage = 50);

    /**
     * @param Token $token
     * @param Request $request
     * @return Request
     */
    public function store(Token $token, Request $request);

    /**
     * @param Token $token
     * @param Request $request
     * @return Request
     */
    public function delete(Token $token, Request $request);

    /**
     * @param Token $token
     * @return Request
     */
    public function deleteByToken(Token $token);

}