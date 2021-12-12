<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\SemaphoreEnum;
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
        $shipArray = $this->acquireSemaphore($shipId, null);

        $ship = $shipArray[$shipId];

        if ($ship === null) {
            throw new ShipDoesNotExistException();
        }

        if ($ship->getIsDestroyed()) {
            throw new ShipIsDestroyedException();
        }

        if ($ship->getUser()->getId() === $userId) {
            return $ship;
        }

        if ($this->hasCrewmanOfUser($ship, $userId)) {
            if (!$allowUplink) {
                throw new UnallowedUplinkOperation();
            }
        } else {
            throw new AccessViolation(sprintf("Ship owned by another user! Fool: %d", $userId));
        }

        return $ship;
    }

    public function getByIdAndUserAndTarget(int $shipId, int $userId, int $targetId, bool $allowUplink = false): array
    {
        $shipArray = $this->acquireSemaphore($shipId, $targetId);

        $ship = $shipArray[$shipId];

        if ($ship === null) {
            throw new ShipDoesNotExistException();
        }

        if ($ship->getIsDestroyed()) {
            throw new ShipIsDestroyedException();
        }

        if ($ship->getUser()->getId() === $userId) {
            return $ship;
        }

        if ($this->hasCrewmanOfUser($ship, $userId)) {
            if (!$allowUplink) {
                throw new UnallowedUplinkOperation();
            }
        } else {
            throw new AccessViolation(sprintf("Ship owned by another user! Fool: %d", $userId));
        }

        return $shipArray;
    }

    public function find(int $shipId): ?ShipInterface
    {
        return $this->acquireSemaphore($shipId, null)[$shipId];
    }

    public function save(ShipInterface $ship): void
    {
        $this->shipRepository->save($ship);
    }

    private function acquireSemaphore(int $shipId, ?int $targetId): array
    {
        $result = [];

        //main ship sema on
        $mainSema = $this->semaphoreUtil->getSemaphore(SemaphoreEnum::MAIN_SHIP_SEMAPHORE_KEY, true);
        $this->semaphoreUtil->acquireMainSemaphore($mainSema);

        $result[$shipId] = $this->acquireSemaphoreWithoutMain($shipId);

        if ($targetId !== null) {
            $result[$targetId] = $this->acquireSemaphoreWithoutMain($targetId);
        }

        //main ship sema off
        $this->semaphoreUtil->releaseSemaphore($mainSema);

        return $result;
    }

    private function acquireSemaphoreWithoutMain(int $shipId): ShipInterface
    {
        $ship = $this->shipRepository->find($shipId);

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
