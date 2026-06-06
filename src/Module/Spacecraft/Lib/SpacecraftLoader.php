<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

/**
 * @implements SpacecraftLoaderInterface<SpacecraftWrapperInterface>
 */
final class SpacecraftLoader implements SpacecraftLoaderInterface
{
    private const int SPACECRAFT_SEMAPHORE_TIMEOUT_SECONDS = 5;

    public function __construct(
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private readonly SemaphoreUtilInterface $semaphoreUtil,
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
            $allowUplink,
            $checkForEntityLock
        );
    }

    /**
     * @return SourceAndTargetWrappersInterface<SpacecraftWrapperInterface>
     */
    private function getByIdAndUserAndTargetIntern(
        int $spacecraftId,
        int $userId,
        ?int $targetId,
        bool $allowUplink,
        bool $checkForEntityLock
    ): SourceAndTargetWrappersInterface {

        if ($checkForEntityLock) {
            $this->checkForEntityLock($spacecraftId);
        }

        $spacecraft = $this->spacecraftRepository->find($spacecraftId);
        if ($spacecraft === null) {
            throw new SpacecraftDoesNotExistException('Raumfahrzeug existiert nicht!');
        }
        $this->checkviolations($spacecraft, $userId, $allowUplink);

        return $this->acquireSemaphores($spacecraft, $targetId);
    }

    private function checkForEntityLock(int $spacecraftId): void
    {
        if ($this->lockManager->isLocked($spacecraftId, LockTypeEnum::SHIP_GROUP)) {
            throw new EntityLockedException('Tick läuft gerade, Zugriff auf Schiff ist daher blockiert');
        }
    }

    private function checkviolations(Spacecraft $spacecraft, int $userId, bool $allowUplink): void
    {
        if ($spacecraft->getUser()->getId() !== $userId) {
            if ($this->crewAssignmentRepository->hasCrewmanOfUser($spacecraft, $userId)) {
                if (!$allowUplink) {
                    throw new UnallowedUplinkOperationException(_('This Operation is not allowed via uplink!'));
                }
                if (!$spacecraft->getSystemState(SpacecraftSystemTypeEnum::UPLINK)) {
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

    #[\Override]
    public function find(int $spacecraftId, bool $checkForEntityLock = true): ?SpacecraftWrapperInterface
    {
        if ($checkForEntityLock) {
            $this->checkForEntityLock($spacecraftId);
        }

        $spacecraft = $this->spacecraftRepository->find($spacecraftId);
        if ($spacecraft === null) {
            return null;
        }

        return $this->acquireSemaphores($spacecraft, null)->getSource();
    }

    /**
     * @return SourceAndTargetWrappersInterface<SpacecraftWrapperInterface>
     */
    private function acquireSemaphores(Spacecraft $spacecraft, ?int $targetId): SourceAndTargetWrappersInterface
    {
        if ($targetId === null && $this->semaphoreUtil->isSemaphoreAlreadyAcquired($spacecraft->getUser()->getId())) {
            return new SourceAndTargetWrappers($this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft));
        }

        $target = $targetId === null ? null : $this->spacecraftRepository->find($targetId);
        $this->acquireSemaphoresForSpacecrafts(
            $target === null ? [$spacecraft] : [$spacecraft, $target]
        );

        $wrapper = $this->createFreshWrapper($spacecraft);
        if ($wrapper === null) {
            throw new RuntimeException('wrapper should not be null here');
        }

        $result = new SourceAndTargetWrappers($wrapper);

        if ($target !== null) {
            $result->setTarget($this->createFreshWrapper($target));
        }

        return $result;
    }

    /**
     * @param array<int, Spacecraft> $spacecrafts
     */
    private function acquireSemaphoresForSpacecrafts(array $spacecrafts): void
    {
        /** @var array<int, Spacecraft> $spacecraftsBySemaphoreKey */
        $spacecraftsBySemaphoreKey = [];
        foreach ($spacecrafts as $spacecraft) {
            $spacecraftsBySemaphoreKey[$spacecraft->getUser()->getId()] ??= $spacecraft;
        }

        ksort($spacecraftsBySemaphoreKey);

        foreach ($spacecraftsBySemaphoreKey as $spacecraft) {
            $this->acquireSemaphoreForSpacecraft($spacecraft);
        }
    }

    private function acquireSemaphoreForSpacecraft(Spacecraft $spacecraft): void
    {
        $key = $spacecraft->getUser()->getId();
        if ($this->semaphoreUtil->isSemaphoreAlreadyAcquired($key)) {
            StuLogger::log(sprintf(
                'Spacecraft semaphore already acquired for user %d and spacecraft %d',
                $key,
                $spacecraft->getId()
            ), LogTypeEnum::SEMAPHORE);
            return;
        }

        StuLogger::log(sprintf(
            'Acquiring spacecraft semaphore for user %d and spacecraft %d',
            $key,
            $spacecraft->getId()
        ), LogTypeEnum::SEMAPHORE);
        StuLogger::log(sprintf(
            'spacecraft %d with key %d',
            $spacecraft->getId(),
            $key
        ), LogTypeEnum::SEMAPHORE);

        $startTime = microtime(true);
        $this->semaphoreUtil->acquireSemaphore($key, self::SPACECRAFT_SEMAPHORE_TIMEOUT_SECONDS);

        StuLogger::log(sprintf(
            'Spacecraft semaphore acquired for user %d and spacecraft %d, waited %F seconds',
            $key,
            $spacecraft->getId(),
            microtime(true) - $startTime
        ), LogTypeEnum::SEMAPHORE);
    }

    private function createFreshWrapper(Spacecraft $spacecraft): ?SpacecraftWrapperInterface
    {
        $spacecraft = $this->spacecraftRepository->findFresh($spacecraft->getId());
        if ($spacecraft === null) {
            return null;
        }

        return $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
    }
}
