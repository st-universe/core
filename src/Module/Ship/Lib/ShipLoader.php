<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\SemaphoreEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipLoader implements ShipLoaderInterface
{
    private ShipRepositoryInterface $shipRepository;

    private SemaphoreUtilInterface $semaphoreUtil;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        SemaphoreUtilInterface $semaphoreUtil
    ) {
        $this->shipRepository = $shipRepository;
        $this->semaphoreUtil = $semaphoreUtil;
    }

    public function getByIdAndUser(int $shipId, int $userId, bool $allowUplink = false): ShipInterface
    {
        $shipArray = $this->acquireSemaphores($shipId, null);

        $this->checkviolations($shipArray[$shipId], $userId, $allowUplink);

        return $shipArray[$shipId];
    }

    public function getByIdAndUserAndTarget(int $shipId, int $userId, int $targetId, bool $allowUplink = false): array
    {
        $shipArray = $this->acquireSemaphores($shipId, $targetId);

        $this->checkviolations($shipArray[$shipId], $userId, $allowUplink);

        return $shipArray;
    }

    private function checkviolations(?ShipInterface $ship, int $userId, bool $allowUplink): void
    {
        if ($ship === null) {
            throw new ShipDoesNotExistException();
        }

        if ($ship->getIsDestroyed()) {
            throw new ShipIsDestroyedException();
        }

        if ($ship->getUser()->getId() !== $userId) {
            if ($this->hasCrewmanOfUser($ship, $userId)) {
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
                throw new AccessViolation(sprintf("Ship owned by another user! Fool: %d", $userId));
            }
        }
    }

    public function find(int $shipId): ?ShipInterface
    {
        return $this->acquireSemaphores($shipId, null)[$shipId];
    }

    public function save(ShipInterface $ship): void
    {
        $this->shipRepository->save($ship);
    }

    private function acquireSemaphores(int $shipId, ?int $targetId): array
    {
        $result = [];

        //main ship sema on
        $mainSema = $this->semaphoreUtil->getSemaphore(SemaphoreEnum::MAIN_SHIP_SEMAPHORE_KEY, true);
        $this->semaphoreUtil->acquireMainSemaphore($mainSema);

        $result[$shipId] = $this->acquireSemaphoresWithoutMain($shipId);

        if ($targetId !== null) {
            $result[$targetId] = $this->acquireSemaphoresWithoutMain($targetId);
        }

        //main ship sema off
        $this->semaphoreUtil->releaseSemaphore($mainSema);

        return $result;
    }

    private function acquireSemaphoresWithoutMain(int $shipId): ?ShipInterface
    {
        $ship = $this->shipRepository->find($shipId);

        if ($ship === null) {
            return null;
        }

        //fleet or ship semas
        if ($ship->getFleet() !== null) {
            foreach ($ship->getFleet()->getShips() as $fleetShip) {
                $fleetShipId = $fleetShip->getId();
                $semaphore = $this->semaphoreUtil->getSemaphore($fleetShipId);
                $this->semaphoreUtil->acquireSemaphore($fleetShipId, $semaphore);
            }
        } else {
            $semaphore = $this->semaphoreUtil->getSemaphore($shipId);
            $this->semaphoreUtil->acquireSemaphore($shipId, $semaphore);
        }

        return $ship;
    }

    private function hasCrewmanOfUser(ShipInterface $ship, int $userId)
    {
        foreach ($ship->getCrewlist() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser()->getId() === $userId) {
                return true;
            }
        }

        return false;
    }
}
