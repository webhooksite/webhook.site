<?php

namespace App\Http\Middleware;

use App\Jobs\BlockIp;
use Carbon\Carbon;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository;
use Illuminate\Routing\Middleware\ThrottleRequests;

class ThrottleRequestsUfw extends ThrottleRequests
{
    /**
     * @var Repository
     */
    private $cache;

    public function __construct(RateLimiter $limiter, Repository $cache)
    {
        parent::__construct($limiter);
        $this->cache = $cache;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            $this->block($request->ip());
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes);

        $response = $next($request);

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * @param string $ip
     */
    private function block($ip)
    {
        if (!$this->cache->has(BlockIp::getCacheKey($ip))) {
            dispatch(new BlockIp($ip));
            $this->cache->add(BlockIp::getCacheKey($ip), 1, Carbon::now()->addMinutes(10));
        }
    }
}