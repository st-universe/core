<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use ColfieldData;
use Colfields;
use PM;

final class FinishBuildJobs implements ProcessTickInterface
{
    public function work(): void
    {
        $result = Colfields::getListBy("aktiv>1 AND aktiv<" . time());
        foreach ($result as $key => $field) {
            /**
             * @var ColfieldData $field
             */

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
            $field->save();
            $txt = "Kolonie " . $field->getColony()->getNameWithoutMarkup() . ": " . $field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " fertiggestellt";
            PM::sendPM(USER_NOONE, $field->getColony()->getUserId(), $txt, PM_SPECIAL_COLONY);
        }
    }
}