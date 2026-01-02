<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Mining;

use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Repository\MiningQueueRepositoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class CancelMining implements CancelMiningInterface
{

    public function __construct(
        private MiningQueueRepositoryInterface $miningQueueRepository
    ) {}

    #[\Override]
    public function cancelMining(ShipWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();

        $state = $ship->getState();
        if ($state === SpacecraftStateEnum::GATHER_RESOURCES) {
            if ($ship->isSystemHealthy(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR)) {
                $wrapper->getSpacecraftSystemManager()->deactivate($wrapper, SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR, true);
            }

            $miningQueue = $this->miningQueueRepository->getByShip($ship->getId());
            if ($miningQueue !== null) {
                $this->miningQueueRepository->truncateByShipId($ship->getId());
            }
            $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
        }
        return false;
    }
}
