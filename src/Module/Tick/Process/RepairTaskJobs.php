<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class RepairTaskJobs implements ProcessTickHandlerInterface
{
    public function __construct(
        private RepairTaskRepositoryInterface $repairTaskRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private RepairUtilInterface $repairUtil,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function work(): void
    {
        $result = $this->repairTaskRepository->getFinishedRepairTasks();
        foreach ($result as $repairTask) {
            $spacecraft = $repairTask->getSpacecraft();

            if (!$spacecraft->hasEnoughCrew()) {
                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $spacecraft->getUser()->getId(),
                    sprintf(
                        _('Ungenügend Crew auf der %s vorhanden, daher wurde die Reparatur des Systems %s abgebrochen'),
                        $spacecraft->getName(),
                        $repairTask->getSystemType()->getDescription()
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                    $spacecraft
                );

                $this->repairTaskRepository->delete($repairTask);

                continue;
            }

            $isSuccess = $this->repairUtil->selfRepair($spacecraft, $repairTask);
            $this->spacecraftRepository->save($spacecraft);

            if ($isSuccess) {
                $msg = sprintf(
                    _('Die Crew der %s hat das System %s auf %d %% reparieren können'),
                    $spacecraft->getName(),
                    $repairTask->getSystemType()->getDescription(),
                    $repairTask->getHealingPercentage()
                );
            } else {
                $msg = sprintf(
                    _('Der Reparaturversuch des Systems %s an Bord der %s brachte keine Besserung'),
                    $repairTask->getSystemType()->getDescription(),
                    $spacecraft->getName()
                );
            }

            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $spacecraft->getUser()->getId(),
                $msg,
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $spacecraft
            );
        }
    }
}
