<?php

namespace App\Http\Middleware;

use App\Jobs\BlockIp;
use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;

class ThrottleRequestsUfw extends ThrottleRequests
{
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
        dispatch(new BlockIp($ip));
    }
}