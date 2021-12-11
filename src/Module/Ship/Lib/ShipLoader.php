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
        $this->acquireSemaphore($shipId);
        $ship = $this->shipRepository->find($shipId);

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

    public function find(int $shipId): ?ShipInterface
    {
        $this->acquireSemaphore($shipId);
        return $this->shipRepository->find($shipId);
    }

    public function save(ShipInterface $ship): void
    {
        $this->shipRepository->save($ship);
    }

    private function acquireSemaphore(int $shipId): void
    {
        //main ship sema on
        $mainSema = $this->semaphoreUtil->getSemaphore(SemaphoreEnum::MAIN_SHIP_SEMAPHORE_KEY);
        $this->semaphoreUtil->acquireMainSemaphore($mainSema);

        //specific ship sema
        $semaphore = $this->semaphoreUtil->getSemaphore($shipId);
        $this->semaphoreUtil->acquireSemaphore($shipId, $semaphore);

        //main ship sema off
        $this->semaphoreUtil->releaseSemaphore($mainSema);
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
