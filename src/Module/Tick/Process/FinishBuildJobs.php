<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Game\GameEnum;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class FinishBuildJobs implements ProcessTickInterface
{
    private $planetFieldRepository;

    private $privateMessageSender;

    private $colonyRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->colonyRepository = $colonyRepository;
    }

    public function work(): void
    {
        $result = $this->planetFieldRepository->getByConstructionFinish(time());
        foreach ($result as $field) {
            $colony = $field->getColony();

            $field->setActive(0);
            if ($field->getBuilding()->isActivateAble() && $colony->getWorkless() >= $field->getBuilding()->getWorkers()) {
                $field->setActive(1);
                $colony->upperWorkers($field->getBuilding()->getWorkers());
                $colony->lowerWorkless($field->getBuilding()->getWorkers());
                $colony->upperMaxBev($field->getBuilding()->getHousing());
            }
            $colony->upperMaxStorage($field->getBuilding()->getStorage());
            $colony->upperMaxEps($field->getBuilding()->getEpsStorage());

            $this->colonyRepository->save($colony);

            $field->setIntegrity($field->getBuilding()->getIntegrity());

            $this->planetFieldRepository->save($field);

            $txt = "Kolonie " . $colony->getName() . ": " . $field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " fertiggestellt";

            $this->privateMessageSender->send(GameEnum::USER_NOONE, (int)$colony->getUserId(), $txt,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY);
        }
    }
}
