<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Stu\Module\Ship\Action\BuildConstruction\BuildConstruction;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class StationUtility implements StationUtilityInterface
{
    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
    }
    public static function canShipBuildConstruction(ShipInterface $ship): bool
    {
        if (!$ship->isShuttleRampHealthy()) {
            return false;
        }

        // check if ship has the required workbee amount
        $workbeeCount = 0;
        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isWorkbee()) {
                $workbeeCount += $stor->getAmount();
            }
        }
        if ($workbeeCount < BuildConstruction::NEEDED_WORKBEES) {
            return false;
        }

        // check if ship has the needed resources
        foreach (BuildConstruction::NEEDED_RESOURCES as $key => $amount) {
            if (
                !$ship->getStorage()->containsKey($key)
                || $ship->getStorage()->get($key)->getAmount() < $amount
            ) {
                return false;
            }
        }

        return true;
    }

    public function getStationBuildplansByUser(int $userId): array
    {
        return $this->shipBuildplanRepository->getStationBuildplansByUser($userId);
    }

    public function getBuidplanIfResearchedByUser(int $planId, int $userId): ?ShipBuildplanInterface
    {
        $plans = $this->getStationBuildplansByUser($userId);

        foreach ($plans as $plan) {
            if ($plan->getId() === $planId) {
                return $plan;
            }
        }

        return null;
    }

    public function getDockedWorkbeeCount(ShipInterface $ship): int
    {
        $dockedWorkbees = 0;
        foreach ($ship->getDockedShips() as $docked) {
            $commodity = $docked->getRump()->getCommodity();
            if ($commodity !== null && $commodity->isWorkbee()) {
                $dockedWorkbees += 1;
            }
        }

        return $dockedWorkbees;
    }

    public function hasEnoughDockedWorkbees(ShipInterface $ship, ShipRumpInterface $rump): bool
    {
        return $this->getDockedWorkbeeCount($ship) >= $rump->getNeededWorkbees();
    }
}
