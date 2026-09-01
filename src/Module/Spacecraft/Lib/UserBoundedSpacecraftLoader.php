<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * @implements SpacecraftLoaderInterface<SpacecraftWrapperInterface>
 */
//TODO rename to SpacecraftLoader
final class UserBoundedSpacecraftLoader implements SpacecraftLoaderInterface
{
    /**
     * this cache is used to avoid multiple queries for the same spacecraft in one request
     * @var array<int, SpacecraftWrapperInterface> */
    private array $cache = [];

    public function __construct(
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly StuConfigInterface $config,
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

        $userIds = $this->spacecraftRepository->getUserIdsForSpacecrafts([$spacecraftId]);
        if (!$this->config->getDbSettings()->useSqlite()) {
            $this->userRepository->lockUsersForUpdate($userIds);
        }

        $spacecraft = $this->spacecraftRepository->find($spacecraftId);
        if ($spacecraft === null) {
            return null;
        }
        if (!in_array($spacecraft->getUser()->getId(), $userIds)) {
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

        if (array_key_exists($spacecraftId, $this->cache)) {
            $resultFromCache = new SourceAndTargetWrappers($this->cache[$spacecraftId]);
            if ($targetId !== null && array_key_exists($targetId, $this->cache)) {
                $resultFromCache->setTarget($this->cache[$targetId]);
            }

            return $resultFromCache;
        }

        StuLogger::log(sprintf(
            'user %3d - Loading Spacecraft %6d (target: %6s, targetUser: %4s)',
            $userId,
            $spacecraftId,
            $targetId ?? 'null',
            $targetUserId ?? 'null'
        ), LogTypeEnum::USER_LOCK);

        $spacecraftIds = [$spacecraftId];
        if ($targetId !== null) {
            $spacecraftIds[] = $targetId;
        }
        $userIds = $this->spacecraftRepository->getUserIdsForSpacecrafts($spacecraftIds);

        if ($targetUserId !== null) {
            $userIds[] = $targetUserId;
        }

        StuLogger::log(sprintf(
            'user %3d - Found following userIds to lock: %s',
            $userId,
            implode(', ', $userIds)
        ), LogTypeEnum::USER_LOCK);

        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        sort($userIds, SORT_NUMERIC);

        $startTime = microtime(true);

        if (!$this->config->getDbSettings()->useSqlite()) {
            $this->userRepository->lockUsersForUpdate($userIds);
        }

        StuLogger::log(sprintf(
            'user %3d - Locking took %F seconds for users: %s',
            $userId,
            microtime(true) - $startTime,
            implode(', ', $userIds)
        ), LogTypeEnum::USER_LOCK);

        $sourceSpacecraft = $this->spacecraftRepository->find($spacecraftId);
        if ($sourceSpacecraft === null) {
            throw new SpacecraftDoesNotExistException('Raumfahrzeug existiert nicht!');
        }

        $this->checkViolations($sourceSpacecraft, $userId, $allowUplink);

        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($sourceSpacecraft);

        $result = new SourceAndTargetWrappers($wrapper);
        $this->cache[$spacecraftId] = $wrapper;

        if ($targetId !== null) {
            $targetSpacecraft = $this->spacecraftRepository->find($targetId);
            if ($targetSpacecraft !== null && in_array($targetSpacecraft->getUser()->getId(), $userIds, true)) {
                $targetWrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($targetSpacecraft);
                $result->setTarget($targetWrapper);
                $this->cache[$targetId] = $targetWrapper;
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
