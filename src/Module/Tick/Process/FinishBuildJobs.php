<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Module\Communication\Lib\PrivateMessageSender;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class FinishBuildJobs implements ProcessTickInterface
{
    private $planetFieldRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function work(): void
    {
        $result = $this->planetFieldRepository->getByConstructionFinish(time());
        foreach ($result as $field) {
            $field->setActive(0);
            if ($field->getBuilding()->isActivateAble() && $field->getColony()->getWorkless() >= $field->getBuilding()->getWorkers()) {
                $field->setActive(1);
                $field->getColony()->upperWorkers($field->getBuilding()->getWorkers());
                $field->getColony()->lowerWorkless($field->getBuilding()->getWorkers());
                $field->getColony()->upperMaxBev($field->getBuilding()->getHousing());
            }
            $field->getColony()->upperMaxStorage($field->getBuilding()->getStorage());
            $field->getColony()->upperMaxEps($field->getBuilding()->getEpsStorage());
            $field->getColony()->save();
            $field->setIntegrity($field->getBuilding()->getIntegrity());

            $this->planetFieldRepository->save($field);

            $txt = "Kolonie " . $field->getColony()->getNameWithoutMarkup() . ": " . $field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " fertiggestellt";
            PrivateMessageSender::sendPM(USER_NOONE, $field->getColony()->getUserId(), $txt, PM_SPECIAL_COLONY);
        }
    }
}