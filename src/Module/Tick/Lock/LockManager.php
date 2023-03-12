<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Lock;

use Stu\Exception\InvalidParamException;
use Stu\Module\Config\StuConfigInterface;

final class LockManager implements LockManagerInterface
{
    private StuConfigInterface $config;

    public function __construct(StuConfigInterface $config)
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
            $this->config->getGameSettings()->getTempDir(),
            LockEnum::getLockPathIdentifier($lockType),
            $batchGroupId
        );
    }

    private function getGroupCount(int $lockType): int
    {
        switch ($lockType) {
            case LockEnum::LOCK_TYPE_COLONY_GROUP:
                return $this->config->getGameSettings()->getColonySettings()->getTickWorker();
            default:
                throw new InvalidParamException('lockType does not exist');
        }
    }
}
