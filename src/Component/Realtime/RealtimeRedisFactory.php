<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use Redis;
use Throwable;
use Stu\Module\Config\StuConfigInterface;

final class RealtimeRedisFactory
{
    public function __construct(private StuConfigInterface $config) {}

    public function create(): ?Redis
    {
        $redis = new Redis();
        $settings = $this->config->getCacheSettings();

        try {
            $socket = $settings->getRedisSocket();
            if ($socket !== null && $socket !== '') {
                try {
                    $redis->connect($socket);
                    return $redis;
                } catch (Throwable) {
                    // fall through to host/port below
                }
            }

            $redis->connect($settings->getRedisHost(), $settings->getRedisPort(), 0.2);

            return $redis;
        } catch (Throwable) {
            return null;
        }
    }
}
