<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use PM;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class FinishTerraformingJobs implements ProcessTickInterface
{
    private $colonyTerraformingRepository;

    private $planetFieldRepository;

    public function __construct(
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function work(): void
    {
        $result = $this->colonyTerraformingRepository->getFinishedJobs();
        foreach ($result as $field) {
            $colonyField = $field->getField();
            $colony = $field->getColony();

            $colonyField->setFieldType($field->getTerraforming()->getToFieldTypeId());
            $colonyField->setTerraformingId(null);

            $this->planetFieldRepository->save($colonyField);

            $this->colonyTerraformingRepository->delete($field);
            $txt = "Kolonie " . $colony->getNameWithoutMarkup() . ": " . $field->getTerraforming()->getDescription() . " auf Feld " . $colonyField->getFieldId() . " abgeschlossen";
            PM::sendPM(USER_NOONE, $colony->getUserId(), $txt, PM_SPECIAL_COLONY);
        }
    }
}