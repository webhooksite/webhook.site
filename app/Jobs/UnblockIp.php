<?php

namespace App\Jobs;

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
     */
    public function handle(LoggerInterface $log)
    {
        $process = new Process(sprintf('sudo ufw delete deny from %s', $this->ip));
        $process->run();

        $log->info('Unblocking ip', [
            'ip' => $this->ip,
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
        ]);
    }
}
