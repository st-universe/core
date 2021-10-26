<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\Selfrepair\SelfrepairUtilInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class RepairTaskJobs implements ProcessTickInterface
{
    private RepairTaskRepositoryInterface $repairTaskRepository;

    private ShipRepositoryInterface $shipRepository;

    private SelfrepairUtilInterface $selfrepairUtil;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        RepairTaskRepositoryInterface $repairTaskRepository,
        ShipRepositoryInterface $shipRepository,
        SelfrepairUtilInterface $selfrepairUtil,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->repairTaskRepository = $repairTaskRepository;
        $this->shipRepository = $shipRepository;
        $this->selfrepairUtil = $selfrepairUtil;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function work(): void
    {
        $result = $this->repairTaskRepository->getFinishedRepairTasks();
        foreach ($result as $repairTask) {
            $ship = $repairTask->getShip();

            if (!$ship->hasEnoughCrew()) {
                continue;
            }

            $isSuccess = $this->selfrepairUtil->selfRepair($ship, $repairTask);
            $this->shipRepository->save($ship);

            $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId());


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
                GameEnum::USER_NOONE,
                $ship->getUser()->getId(),
                $msg,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }
    }
}
