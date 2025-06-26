<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Retrofit;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;


final class CancelRetrofit implements CancelRetrofitInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository) {}


    #[Override]
    public function cancelRetrofit(Ship $ship): bool
    {
        $state = $ship->getState();
        if ($state === SpacecraftStateEnum::RETROFIT) {
            $this->setStateNoneAndSave($ship);

            $this->colonyShipQueueRepository->truncateByShip($ship->getId());

            return true;
        }

        return false;
    }

    private function setStateNoneAndSave(Ship $ship): void
    {
        $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
        $this->shipRepository->save($ship);
    }
}
