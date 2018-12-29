<?php

namespace App\Jobs;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class UnblockIp extends Job implements ShouldQueue
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
        if (filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ^ FILTER_FLAG_IPV6) === false) {
            $log->warning('UnblockIp: Invalid IP address', ['ip' => $this->ip]);
            return;
        }

        $process = new Process(sprintf('sudo ufw delete deny from %s', $this->ip));
        $process->run();

        $log->info('Unblocking ip', [
            'ip' => $this->ip,
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
        ]);

        $cache->forget(sprintf('block:%s', $this->ip));
    }
}
