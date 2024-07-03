<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class RepairTaskJobs implements ProcessTickHandlerInterface
{
    public function __construct(private RepairTaskRepositoryInterface $repairTaskRepository, private ShipRepositoryInterface $shipRepository, private RepairUtilInterface $repairUtil, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function work(): void
    {
        $result = $this->repairTaskRepository->getFinishedRepairTasks();
        foreach ($result as $repairTask) {
            $ship = $repairTask->getShip();

            $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $ship->getId());

            if (!$ship->hasEnoughCrew()) {
                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $ship->getUser()->getId(),
                    sprintf(
                        _('Ungenügend Crew auf der %s vorhanden, daher wurde die Reparatur des Systems %s abgebrochen'),
                        $ship->getName(),
                        $repairTask->getSystemType()->getDescription()
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                    $href
                );

                $this->repairTaskRepository->delete($repairTask);

                continue;
            }

            $isSuccess = $this->repairUtil->selfRepair($ship, $repairTask);
            $this->shipRepository->save($ship);

            if ($isSuccess) {
                $msg = sprintf(
                    _('Die Crew der %s hat das System %s auf %d %% reparieren können'),
                    $ship->getName(),
                    $repairTask->getSystemType()->getDescription(),
                    $repairTask->getHealingPercentage()
                );
            } else {
                $msg = sprintf(
                    _('Der Reparaturversuch des Systems %s an Bord der %s brachte keine Besserung'),
                    $repairTask->getSystemType()->getDescription(),
                    $ship->getName()
                );
            }

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $ship->getUser()->getId(),
                $msg,
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $href
            );
        }
    }
}
