<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use RuntimeException;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappers;
use Stu\Module\Spacecraft\Lib\SourceAndTargetWrappersInterface;
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

        //main spacecraft sema on
        StuLogger::log(sprintf(
            'Acquiring main semaphore for user %d and spacecraft %d%s',
            $spacecraft->getUser()->getId(),
            $spacecraft->getId(),
            $targetId !== null ? ', target ' . $targetId : ''
        ), LogTypeEnum::SEMAPHORE);

        $startTime = microtime(true);
        $mainSema = $this->semaphoreUtil->acquireSemaphore(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY);

        StuLogger::log(sprintf(
            'Main semaphore acquired for user %d, waited %F seconds',
            $spacecraft->getUser()->getId(),
            microtime(true) - $startTime
        ), LogTypeEnum::SEMAPHORE);

        StuLogger::log(sprintf(
            'Acquiring spacecraft semaphore for user %d and spacecraft %d',
            $spacecraft->getUser()->getId(),
            $spacecraft->getId()
        ), LogTypeEnum::SEMAPHORE);

        $startTime = microtime(true);
        $wrapper = $this->acquireSemaphoreForSpacecraft($spacecraft);
        if ($wrapper === null) {
            throw new RuntimeException('wrapper should not be null here');
        }

        StuLogger::log(sprintf(
            'Spacecraft semaphore acquired for user %d and spacecraft %d, waited %F seconds',
            $spacecraft->getUser()->getId(),
            $spacecraft->getId(),
            microtime(true) - $startTime
        ), LogTypeEnum::SEMAPHORE);

        $result = new SourceAndTargetWrappers($wrapper);

        if ($targetId !== null) {

            StuLogger::log(sprintf(
                'Acquiring target spacecraft semaphore for user %d and target %d',
                $spacecraft->getUser()->getId(),
                $targetId
            ), LogTypeEnum::SEMAPHORE);

            $startTime = microtime(true);
            $result->setTarget($this->acquireSemaphoreForSpacecraft($targetId));

            StuLogger::log(sprintf(
                'Target spacecraft semaphore acquired for user %d and target %d, waited %F seconds',
                $spacecraft->getUser()->getId(),
                $targetId,
                microtime(true) - $startTime
            ), LogTypeEnum::SEMAPHORE);
        }

        //main spacecraft sema off
        $this->semaphoreUtil->releaseSemaphore($mainSema);
        StuLogger::log(sprintf(
            'Main semaphore released for user %d',
            $spacecraft->getUser()->getId()
        ), LogTypeEnum::SEMAPHORE);

        return $result;
    }

    private function acquireSemaphoreForSpacecraft(Spacecraft|int $spacecraft): ?SpacecraftWrapperInterface
    {
        $spacecraft = $spacecraft instanceof Spacecraft
            ? $spacecraft
            : $this->spacecraftRepository->find($spacecraft);

        if ($spacecraft === null) {
            return null;
        }

        $key = $spacecraft->getUser()->getId();
        StuLogger::log(sprintf(
            'spacecraft %d with key %d',
            $spacecraft->getId(),
            $key
        ), LogTypeEnum::SEMAPHORE);
        $this->semaphoreUtil->acquireSemaphore($key);

        return $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
    }
}
