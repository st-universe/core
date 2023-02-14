<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Lock;

use Noodlehaus\ConfigInterface;

final class LockManager implements LockManagerInterface
{
    private const DEFAULT_GROUP_COUNT = 1;

    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function setLock(int $batchGroupId, int $lockType): void
    {
        @touch($this->getLockPath($batchGroupId, $lockType));
    }

    public function clearLock(int $batchGroupId, int $lockType): void
    {
        @unlink($this->getLockPath($batchGroupId, $lockType));
    }

    public function isLocked(int $entityId, int $lockType): bool
    {
        return @file_exists($this->getLockPath($entityId % $this->getGroupCount($lockType) + 1, $lockType));
    }

    private function getLockPath(int $batchGroupId, int $lockType): string
    {
        return sprintf(
            '%s/%s_%d.lock',
            $this->config->get('game.temp_dir'),
            LockEnum::getLockPathIdentifier($lockType),
            $batchGroupId
        );
    }

    private function getGroupCount(int $lockType): int
    {
        return (int)$this->config->get(LockEnum::getLockGroupConfigPath($lockType), self::DEFAULT_GROUP_COUNT);
    }
}
