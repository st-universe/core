<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Ship\Lib\ShipRetrofitInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FinishShipRetrofitJobs implements ProcessTickHandlerInterface
{
    public function __construct(private ShipRetrofitInterface $shipRetrofit, private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository, private PrivateMessageSenderInterface $privateMessageSender, private ShipRepositoryInterface $shipRepository) {}

    #[Override]
    public function work(): void
    {
        $queue = $this->colonyShipQueueRepository->getFinishedJobs();
        foreach ($queue as $obj) {
            if ($obj->getMode() == 2) {

                $colony = $obj->getColony();

                $newbuildplan = $obj->getShipBuildplan();
                $ship = $obj->getShip();

                if ($ship == null) {
                    return;
                }

                $this->shipRetrofit->updateBy(
                    $ship,
                    $newbuildplan
                );
                $this->colonyShipQueueRepository->delete($obj);

                $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

                $this->shipRepository->save($ship);

                $txt = _("Auf der Kolonie " . $colony->getName() . " wurde die " . $ship->getName() . " umgerüstet");

                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $colony->getUserId(),
                    $txt,
                    PrivateMessageFolderTypeEnum::SPECIAL_COLONY
                );
            }
        }
    }
}