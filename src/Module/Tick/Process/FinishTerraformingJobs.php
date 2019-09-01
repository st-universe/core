<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use PM;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;

final class FinishTerraformingJobs implements ProcessTickInterface
{
    private $colonyTerraformingRepository;

    public function __construct(
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository
    ) {
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
    }

    public function work(): void
    {
        $result = $this->colonyTerraformingRepository->getFinishedJobs();
        foreach ($result as $field) {
            $colonyField = $field->getField();
            $colony = $field->getColony();

            $colonyField->setFieldType($field->getTerraforming()->getToFieldTypeId());
            $colonyField->setTerraformingId(0);
            $colonyField->save();

            $this->colonyTerraformingRepository->delete($field);
            $txt = "Kolonie " . $colony->getNameWithoutMarkup() . ": " . $field->getTerraforming()->getDescription() . " auf Feld " . $colonyField->getFieldId() . " abgeschlossen";
            PM::sendPM(USER_NOONE, $colony->getUserId(), $txt, PM_SPECIAL_COLONY);
        }
    }
}