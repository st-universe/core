<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use RuntimeException;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Exception\EntityLockedException;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipLoader implements ShipLoaderInterface
{
    private ShipRepositoryInterface $shipRepository;

    private SemaphoreUtilInterface $semaphoreUtil;

    private GameControllerInterface $game;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private LockManagerInterface $lockManager;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        SemaphoreUtilInterface $semaphoreUtil,
        GameControllerInterface $game,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        LockManagerInterface $lockManager
    ) {
        $this->shipRepository = $shipRepository;
        $this->semaphoreUtil = $semaphoreUtil;
        $this->game = $game;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->lockManager = $lockManager;
    }

    public function getByIdAndUser(
        int $shipId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): ShipInterface {
        return $this->getByIdAndUserAndTargetIntern(
            $shipId,
            $userId,
            null,
            $allowUplink,
            $checkForEntityLock
        )->getSource()->get();
    }

    public function getWrapperByIdAndUser(
        int $shipId,
        int $userId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): ShipWrapperInterface {
        return $this->getByIdAndUserAndTargetIntern(
            $shipId,
            $userId,
            null,
            $allowUplink,
            $checkForEntityLock
        )->getSource();
    }

    public function getWrappersBySourceAndUserAndTarget(
        int $shipId,
        int $userId,
        int $targetId,
        bool $allowUplink = false,
        bool $checkForEntityLock = true
    ): SourceAndTargetWrappersInterface {
        return $this->getByIdAndUserAndTargetIntern(
            $shipId,
            $userId,
            $targetId,
            $allowUplink,
            $checkForEntityLock
        );
    }

    private function getByIdAndUserAndTargetIntern(
        int $shipId,
        int $userId,
        ?int $targetId,
        bool $allowUplink,
        bool $checkForEntityLock
    ): SourceAndTargetWrappersInterface {

        if ($checkForEntityLock && $this->lockManager->isLocked($shipId, LockTypeEnum::SHIP_GROUP)) {
            throw new EntityLockedException('Tick läuft gerade, Zugriff auf Schiff ist daher blockiert');
        }

        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            throw new ShipDoesNotExistException(_('Ship does not exist!'));
        }
        $this->checkviolations($ship, $userId, $allowUplink);

        return $this->acquireSemaphores($ship, $targetId);
    }

    private function checkviolations(ShipInterface $ship, int $userId, bool $allowUplink): void
    {
        if ($ship->isDestroyed()) {
            throw new ShipIsDestroyedException(_('Ship is destroyed!'));
        }

        if ($ship->getUser()->getId() !== $userId) {
            if ($ship->hasCrewmanOfUser($userId)) {
                if (!$allowUplink) {
                    throw new UnallowedUplinkOperation(_('This Operation is not allowed via uplink!'));
                }
                if (!$ship->getSystemState(ShipSystemTypeEnum::SYSTEM_UPLINK)) {
                    throw new UnallowedUplinkOperation(_('Uplink is not activated!'));
                }
                if ($ship->getUser()->isVacationRequestOldEnough()) {
                    throw new UnallowedUplinkOperation(_('Owner is on vacation!'));
                }
            } else {
                throw new AccessViolation(sprintf("Ship owned by another user (%d)! Fool: %d", $ship->getUser()->getId(), $userId));
            }
        }
    }

    public function find(int $shipId): ?ShipWrapperInterface
    {
        $ship = $this->shipRepository->find($shipId);
        if ($ship === null) {
            return null;
        }

        return $this->acquireSemaphores($ship, null)->getSource();
    }

    public function save(ShipInterface $ship): void
    {
        $this->shipRepository->save($ship);
    }

    private function acquireSemaphores(ShipInterface $ship, ?int $targetId): SourceAndTargetWrappersInterface
    {
        if ($targetId === null && $this->game->isSemaphoreAlreadyAcquired($ship->getUser()->getId())) {
            return new SourceAndTargetWrappers($this->shipWrapperFactory->wrapShip($ship));
        }

        //main ship sema on
        $mainSema = $this->semaphoreUtil->getSemaphore(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY);
        $this->semaphoreUtil->acquireMainSemaphore($mainSema);

        $wrapper = $this->acquireSemaphoreForShip($ship, null);
        if ($wrapper === null) {
            throw new RuntimeException('wrapper should not be null here');
        }
        $result = new SourceAndTargetWrappers($wrapper);

        if ($targetId !== null) {
            $result->setTarget($this->acquireSemaphoreForShip(null, $targetId));
        }

        //main ship sema off
        $this->semaphoreUtil->releaseSemaphore($mainSema);

        return $result;
    }

    private function acquireSemaphoreForShip(?ShipInterface $ship, ?int $shipId): ?ShipWrapperInterface
    {
        if ($ship === null && $shipId === null) {
            return null;
        }

        if ($ship === null) {
            $ship = $this->shipRepository->find($shipId);
        }

        if ($ship === null) {
            return null;
        }

        $key = $ship->getUser()->getId();
        $semaphore = $this->semaphoreUtil->getSemaphore($key);
        $this->semaphoreUtil->acquireSemaphore($key, $semaphore); //prüft ob schon genommen
        return $this->shipWrapperFactory->wrapShip($ship);
    }
}
