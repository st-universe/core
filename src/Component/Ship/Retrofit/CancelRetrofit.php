<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Retrofit;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;


final class CancelRetrofit implements CancelRetrofitInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository) {}


    #[Override]
    public function cancelRetrofit(ShipInterface $ship): bool
    {
        $state = $ship->getState();
        if ($state === SpacecraftStateEnum::SHIP_STATE_RETROFIT) {
            $this->setStateNoneAndSave($ship);

            $this->colonyShipQueueRepository->truncateByShip($ship->getId());

            return true;
        }

        return false;
    }

    private function setStateNoneAndSave(ShipInterface $ship): void
    {
        $ship->setState(SpacecraftStateEnum::SHIP_STATE_NONE);
        $this->shipRepository->save($ship);
    }
}
