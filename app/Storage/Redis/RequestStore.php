<?php


namespace App\Storage\Redis;

use App\Storage\Request;
use App\Storage\Token;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestStore implements \App\Storage\RequestStore
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * TokenStore constructor.
     */
    public function __construct()
    {
        $this->redis = Redis::connection(config('database.redis.connection'));
    }

    /**
     * @param Token $token
     * @param string $requestId
     * @return Request
     */
    public function find(Token $token, $requestId)
    {
        $result = $this->redis->hget(Request::getIdentifier($token->uuid), $requestId);

        if (!$result) {
            throw new NotFoundHttpException('Request not found');
        }

        $this->redis->expire(Request::getIdentifier($token->uuid), config('app.expiry'));

        return new Request(json_decode($result, true));
    }

    /**
     * @param Token $token
     * @param int $page
     * @param int $perPage
     * @return Collection|static
     */
    public function all(Token $token, $page = 0, $perPage = 50)
    {
        $keys = array_reverse(
            array_slice(
                (array) $this->redis->hkeys(Request::getIdentifier($token->uuid)),
                $page * $perPage,
                $perPage
            )
        );

        if (empty($keys)) {
            return Collection::make();
        }

        /** @var Collection $result */
        return Collection::make(
            array_map(
                function ($item) {
                    return new Request(json_decode($item, true));
                },
                $this->redis->hmget(
                    Request::getIdentifier($token->uuid),
                    $keys
                )
            )
        );
    }

    /**
     * @param Token $token
     * @param Request $request
     * @return Request
     */
    public function store(Token $token, Request $request)
    {
        $result = $this
            ->redis
            ->hmset(
                Request::getIdentifier($token->uuid),
                $request->uuid,
                json_encode($request->attributes())
            );

        $this->redis->expire(Request::getIdentifier($token->uuid), config('app.expiry'));

        return $result;
    }

    /**
     * @param Token $token
     * @param Request $request
     * @return Request
     */
    public function delete(Token $token, Request $request)
    {
        return $this
            ->redis
            ->hdel(
                Request::getIdentifier($token->uuid),
                $request->uuid
            );
    }


}