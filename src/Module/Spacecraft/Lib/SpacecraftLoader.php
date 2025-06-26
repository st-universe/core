<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Override;
use RuntimeException;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\SemaphoreUtilInterface;
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
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private SemaphoreUtilInterface $semaphoreUtil,
        private GameControllerInterface $game,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private LockManagerInterface $lockManager
    ) {}

    #[Override]
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

    #[Override]
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

    #[Override]
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
            throw new SpacecraftDoesNotExistException('Spacecraft does not exist!');
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

    #[Override]
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

    #[Override]
    public function save(Spacecraft $spacecraft): void
    {
        $this->spacecraftRepository->save($spacecraft);
    }

    /**
     * @return SourceAndTargetWrappersInterface<SpacecraftWrapperInterface>
     */
    private function acquireSemaphores(Spacecraft $spacecraft, ?int $targetId): SourceAndTargetWrappersInterface
    {
        if ($targetId === null && $this->game->isSemaphoreAlreadyAcquired($spacecraft->getUser()->getId())) {
            return new SourceAndTargetWrappers($this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft));
        }

        //main spacecraft sema on
        $mainSema = $this->semaphoreUtil->getSemaphore(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY);
        $this->semaphoreUtil->acquireMainSemaphore($mainSema);

        $wrapper = $this->acquireSemaphoreForSpacecraft($spacecraft, null);
        if ($wrapper === null) {
            throw new RuntimeException('wrapper should not be null here');
        }
        $result = new SourceAndTargetWrappers($wrapper);

        if ($targetId !== null) {
            $result->setTarget($this->acquireSemaphoreForSpacecraft(null, $targetId));
        }

        //main spacecraft sema off
        $this->semaphoreUtil->releaseSemaphore($mainSema);

        return $result;
    }

    private function acquireSemaphoreForSpacecraft(?Spacecraft $spacecraft, ?int $spacecraftId): ?SpacecraftWrapperInterface
    {
        if ($spacecraft === null && $spacecraftId === null) {
            return null;
        }

        if ($spacecraft === null) {
            $spacecraft = $this->spacecraftRepository->find($spacecraftId);
        }

        if ($spacecraft === null) {
            return null;
        }

        $key = $spacecraft->getUser()->getId();
        $semaphore = $this->semaphoreUtil->getSemaphore($key);
        $this->semaphoreUtil->acquireSemaphore($key, $semaphore); //prüft ob schon genommen
        return $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
    }
}
