<?php

namespace Stu\Module\Tick\Lock;

interface LockManagerInterface
{
    public function setLock(int $batchGroupId, int $lockType): void;

    public function clearLock(int $batchGroupId, int $lockType): void;

    /**
     * Checks if the entity is locked due to tick operations
     */
    public function isLocked(int $entityId, int $lockType): bool;
}
