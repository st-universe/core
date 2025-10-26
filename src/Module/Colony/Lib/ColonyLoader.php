<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ColonyLoader implements ColonyLoaderInterface
{
    public function __construct(private ColonyRepositoryInterface $colonyRepository, private LockManagerInterface $lockManager)
    {
    }

    #[\Override]
    public function loadWithOwnerValidation(int $colonyId, int $userId, bool $checkForEntityLock = true): Colony
    {
        $colony = $this->loadInternal($colonyId, $checkForEntityLock);

        if ($colony->getUserId() !== $userId) {
            throw new AccessViolationException(sprintf("Colony owned by another user (%d)! Fool: %d", $colony->getUserId(), $userId));
        }

        return $colony;
    }

    #[\Override]
    public function load(int $colonyId, bool $checkForEntityLock = true): Colony
    {
        return $this->loadInternal($colonyId, $checkForEntityLock);
    }

    private function loadInternal(int $colonyId, bool $checkForEntityLock): Colony
    {
        if ($checkForEntityLock && $this->lockManager->isLocked($colonyId, LockTypeEnum::COLONY_GROUP)) {
            throw new EntityLockedException('Tick läuft gerade, Zugriff auf Kolonie ist daher blockiert');
        }

        $colony = $this->colonyRepository->find($colonyId);
        if ($colony === null) {
            throw new AccessViolationException("Colony not existent!");
        }

        return $colony;
    }
}
