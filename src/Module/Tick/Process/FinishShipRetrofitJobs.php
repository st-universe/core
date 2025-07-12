<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Ship\Lib\ShipRetrofitInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FinishShipRetrofitJobs implements ProcessTickHandlerInterface
{
    public function __construct(private ShipRetrofitInterface $shipRetrofit, private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository, private ShipRepositoryInterface $shipRepository) {}

    #[Override]
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

                $this->shipRepository->save($ship);
            }
        }
    }
}
