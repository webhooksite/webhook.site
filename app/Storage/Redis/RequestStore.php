<?php


namespace App\Storage\Redis;

use App\Storage\Request;
use App\Storage\Token;
use Carbon\Carbon;
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
        return collect(
            $this->redis->hgetall(Request::getIdentifier($token->uuid))
        )
        ->filter()
        ->map(
            function ($request) {
                return json_decode($request);
            }
        )->sortBy(
            function ($request) {
                return Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $request->created_at
                )->getTimestamp();
            }
        )->forPage(
            $page,
            $perPage
        )->values();
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

    /**
     * @param Token $token
     * @return Request
     */
    public function deleteByToken(Token $token)
    {
        return $this
            ->redis
            ->del(Request::getIdentifier($token->uuid));
    }

}