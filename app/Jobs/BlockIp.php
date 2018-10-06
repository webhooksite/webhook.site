<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class BlockIp extends Job implements ShouldQueue
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @param string $ip
     */
    public function __construct($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @param LoggerInterface $log
     * @param Repository $cache
     */
    public function handle(LoggerInterface $log, Repository $cache)
    {
        $process = new Process(sprintf('sudo ufw insert 1 deny from %s', $this->ip));
        $process->run();

        $log->info('Blocking ip', [
            'ip' => $this->ip,
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
        ]);

        $job = (new UnblockIp($this->ip))
            ->delay(Carbon::now()->addMinutes(10));

        dispatch($job);

        $log->info('Dispatched UnblockIp');
    }

    /**
     * @param $ip
     * @return string
     */
    public static function getCacheKey($ip)
    {
        return sprintf('block:%s', $ip);
    }
}
