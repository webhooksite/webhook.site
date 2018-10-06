<?php

namespace App\Jobs;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class BlockIp extends Job
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
        $process = new Process(sprintf('ufw insert 1 deny from %s', $this->ip));
        $process->run();

        $log->info('Blocking ip', ['ip' => $this->ip, 'output' => $process->getOutput()]);
    }
}