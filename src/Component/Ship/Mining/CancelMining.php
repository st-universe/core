<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Mining;

use Override;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\MiningQueueRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;



final class CancelMining implements CancelMiningInterface
{

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private MiningQueueRepositoryInterface $miningQueueRepository,
    ) {}

    #[Override]
    public function cancelMining(ShipInterface $ship, ShipWrapperInterface $wrapper): bool
    {


        $state = $ship->getState();
        if ($state === ShipStateEnum::SHIP_STATE_GATHER_RESOURCES) {
            if ($ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR)) {
                $wrapper->getShipSystemManager()->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR, true);
            }

            $miningQueue = $this->miningQueueRepository->getByShip($ship->getId());
            if ($miningQueue !== null) {
                $this->miningQueueRepository->truncateByShipId($ship->getId());
            }
            $this->setStateNoneAndSave($ship);
        }
        return false;
    }

    private function setStateNoneAndSave(ShipInterface $ship): void
    {
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $this->shipRepository->save($ship);
    }
}
