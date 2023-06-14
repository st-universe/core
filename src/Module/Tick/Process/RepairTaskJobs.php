<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class RepairTaskJobs implements ProcessTickHandlerInterface
{
    private RepairTaskRepositoryInterface $repairTaskRepository;

    private ShipRepositoryInterface $shipRepository;

    private RepairUtilInterface $repairUtil;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        RepairTaskRepositoryInterface $repairTaskRepository,
        ShipRepositoryInterface $shipRepository,
        RepairUtilInterface $repairUtil,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->repairTaskRepository = $repairTaskRepository;
        $this->shipRepository = $shipRepository;
        $this->repairUtil = $repairUtil;
        $this->privateMessageSender = $privateMessageSender;
    }

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
                        ShipSystemTypeEnum::getDescription($repairTask->getSystemType())
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
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
                    ShipSystemTypeEnum::getDescription($repairTask->getSystemType()),
                    $repairTask->getHealingPercentage()
                );
            } else {
                $msg = sprintf(
                    _('Der Reparaturversuch des Systems %s an Bord der %s brachte keine Besserung'),
                    ShipSystemTypeEnum::getDescription($repairTask->getSystemType()),
                    $ship->getName()
                );
            }

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $ship->getUser()->getId(),
                $msg,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }
    }
}
