<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Ship\Lib\ShipRetrofitInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;

final class FinishShipRetrofitJobs implements ProcessTickHandlerInterface
{
    public function __construct(
        private ShipRetrofitInterface $shipRetrofit,
        private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository
    ) {}

    #[\Override]
    public function work(): void
    {
        $queue = $this->colonyShipQueueRepository->getFinishedJobs();
        foreach ($queue as $obj) {
            if ($obj->getMode() == 2) {

                $colony = $obj->getColony();

                $newbuildplan = $obj->getSpacecraftBuildplan();
                $ship = $obj->getShip();

                if (
                    $ship === null
                    || $ship->getUserId() !== $obj->getUserId()
                    || $colony->getUserId() !== $obj->getUserId()
                    || $ship->getLocation() !== $colony->getLocation()
                    || $ship->getRump()->getId() !== $obj->getRump()->getId()
                    || $newbuildplan->getRump()->getId() !== $ship->getRump()->getId()
                    || $newbuildplan->getUser()->getId() !== $obj->getUserId()
                    || $ship->getBuildplan()?->getUser()->getId() !== $obj->getUserId()
                    || $ship->getState() !== SpacecraftStateEnum::RETROFIT
                ) {
                    if (
                        $ship !== null
                        && $ship->getUserId() === $obj->getUserId()
                        && $ship->getState() === SpacecraftStateEnum::RETROFIT
                    ) {
                        $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
                    }
                    $this->colonyShipQueueRepository->delete($obj);
                    continue;
                }

                $this->shipRetrofit->updateBy(
                    $ship,
                    $newbuildplan,
                    $colony
                );
                $this->colonyShipQueueRepository->delete($obj);

                $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
            }
        }
    }
}
