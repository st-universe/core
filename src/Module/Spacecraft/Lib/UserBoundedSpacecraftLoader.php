<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use RuntimeException;
use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * @implements SpacecraftLoaderInterface<SpacecraftWrapperInterface>
 */
final class UserBoundedSpacecraftLoader implements SpacecraftLoaderInterface
{
    public function __construct(
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly LockManagerInterface $lockManager
    ) {}

    #[\Override]
    public function getByIdAndUser(
        int $spacecraftId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): Spacecraft {
        return $this->getByIdAndUserAndTargetIntern(
            $spacecraftId,
            $userId,
            null,
            null,
            $allowUplink,
            $checkForEntityLock
        )->getSource()->get();
    }

    #[\Override]
    public function getWrapperByIdAndUser(
        int $spacecraftId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SpacecraftWrapperInterface {
        return $this->getByIdAndUserAndTargetIntern(
            $spacecraftId,
            $userId,
            null,
            null,
            $allowUplink,
            $checkForEntityLock
        )->getSource();
    }

    #[\Override]
    public function getWrapperByIdAndUserAndTargetUser(
        int $spacecraftId,
        int $userId,
        int $targetUserId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SpacecraftWrapperInterface {
        return $this->getByIdAndUserAndTargetIntern(
            $spacecraftId,
            $userId,
            null,
            $targetUserId,
            $allowUplink,
            $checkForEntityLock
        )->getSource();
    }

    #[\Override]
    public function getWrappersBySourceAndUserAndTarget(
        int $spacecraftId,
        int $userId,
        int $targetId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SourceAndTargetWrappersInterface {
        return $this->getByIdAndUserAndTargetIntern(
            $spacecraftId,
            $userId,
            $targetId,
            null,
            $allowUplink,
            $checkForEntityLock
        );
    }

    #[\Override]
    public function find(int $spacecraftId, bool $checkForEntityLock = true): ?SpacecraftWrapperInterface
    {
        if ($checkForEntityLock) {
            $this->checkForEntityLock($spacecraftId);
        }

        $userId = $this->spacecraftRepository->getUserIdsForSpacecrafts([$spacecraftId])[0] ?? null;
        if ($userId === null) {
            return null;
        }
        $this->userRepository->lockUsersForUpdate([$userId]);

        $spacecraft = $this->spacecraftRepository->find($spacecraftId);
        if ($spacecraft === null || $spacecraft->getUser()->getId() !== $userId) {
            return null;
        }

        return $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
    }

    /**
     * @return SourceAndTargetWrappersInterface<SpacecraftWrapperInterface>
     */
    private function getByIdAndUserAndTargetIntern(
        int $spacecraftId,
        int $userId,
        ?int $targetId,
        ?int $targetUserId,
        bool $allowUplink,
        bool $checkForEntityLock
    ): SourceAndTargetWrappersInterface {
        if ($checkForEntityLock) {
            $this->checkForEntityLock($spacecraftId);
        }

        $spacecraftIds = [$spacecraftId];
        if ($targetId !== null) {
            $spacecraftIds[] = $targetId;
        }
        $userIds = $this->spacecraftRepository->getUserIdsForSpacecrafts($spacecraftIds);

        if ($targetUserId !== null) {
            $userIds[] = $targetUserId;
        }

        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        sort($userIds, SORT_NUMERIC);

        $this->userRepository->lockUsersForUpdate($userIds);

        $sourceSpacecraft = $this->spacecraftRepository->find($spacecraftId);
        if ($sourceSpacecraft === null) {
            throw new SpacecraftDoesNotExistException('Raumfahrzeug existiert nicht!');
        }

        $this->checkViolations($sourceSpacecraft, $userId, $allowUplink);

        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($sourceSpacecraft);

        $result = new SourceAndTargetWrappers($wrapper);

        if ($targetId !== null) {
            $targetSpacecraft = $this->spacecraftRepository->find($targetId);
            if ($targetSpacecraft !== null && in_array($targetSpacecraft->getUser()->getId(), $userIds, true)) {
                $targetWrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($targetSpacecraft);
                $result->setTarget($targetWrapper);
            }
        }

        return $result;
    }

    private function checkForEntityLock(int $spacecraftId): void
    {
        if ($this->lockManager->isLocked($spacecraftId, LockTypeEnum::SHIP_GROUP)) {
            throw new EntityLockedException('Tick läuft gerade, Zugriff auf Schiff ist daher blockiert');
        }
    }

    private function checkViolations(Spacecraft $spacecraft, int $userId, bool $allowUplink): void
    {
        if ($spacecraft->getUser()->getId() !== $userId) {
            if ($this->crewAssignmentRepository->hasCrewmanOfUser($spacecraft, $userId)) {
                if (!$allowUplink) {
                    throw new UnallowedUplinkOperationException(_('This Operation is not allowed via uplink!'));
                }
                if (!$spacecraft->getSystemState(\Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum::UPLINK)) {
                    throw new UnallowedUplinkOperationException(_('Uplink is not activated!'));
                }
                if ($spacecraft->getUser()->isVacationRequestOldEnough()) {
                    throw new UnallowedUplinkOperationException(_('Owner is on vacation!'));
                }
            } else {
                throw new AccessViolationException(sprintf("Spacecraft owned by another user (%d)! Fool: %d", $spacecraft->getUser()->getId(), $userId));
            }
        }
    }
}
