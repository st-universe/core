<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use InvalidArgumentException;
use Noodlehaus\ConfigInterface;

final class RealtimeChannels
{
    private readonly string $namespace;

    public function __construct(ConfigInterface $config)
    {
        $namespace = $config->get('realtime.redisNamespace') ?? 'stu';
        if (!is_string($namespace) || preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $namespace) !== 1) {
            throw new InvalidArgumentException('Invalid realtime.redisNamespace');
        }
        $this->namespace = $namespace;
    }

    public function starmapSpacecraftStream(): string
    {
        return $this->namespace . ':realtime:starmap:spacecraft';
    }

    public function starmapCoverageKey(int $userId, int $layerId): string
    {
        return sprintf('%s:realtime:starmap:coverage:%d:%d', $this->namespace, $userId, $layerId);
    }
}
