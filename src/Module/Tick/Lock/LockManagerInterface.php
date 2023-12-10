<?php

namespace Stu\Module\Tick\Lock;

interface LockManagerInterface
{
    public function setLock(int $batchGroupId, LockTypeEnum $type): void;

    public function clearLock(int $batchGroupId, LockTypeEnum $type): void;

    /**
     * Checks if the entity is locked due to tick operations
     */
    public function isLocked(int $entityId, LockTypeEnum $type): bool;
}
