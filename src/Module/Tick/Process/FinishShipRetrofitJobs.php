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

                if ($ship == null) {
                    return;
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
