<?php

namespace App\Events;

class IpThrottled extends Event
{
    /**
     * @var string
     */
    public $ip;

    /**
     * @param string $ip
     */
    public function __construct(string $ip)
    {
        $this->ip = $ip;
    }
}