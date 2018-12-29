<?php

namespace App\Listeners;

use App\Events\IpThrottled;
use App\Jobs\BlockIp as BlockIpJob;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;

class BlockIp
{
    /**
     * @var Repository
     */
    private $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function handle(IpThrottled $event): void
    {
        if (!$this->cache->has(sprintf('block:%s', $event->ip))) {
            dispatch(new BlockIpJob($event->ip));

            $this->cache->add(sprintf('block:%s', $event->ip), 1, Carbon::now()->addMinutes(10));
        }
    }
}