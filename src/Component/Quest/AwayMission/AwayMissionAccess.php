<?php

declare(strict_types=1);

namespace Stu\Component\Quest\AwayMission;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;

final class AwayMissionAccess
{
    public function __construct(
        private AwayMissionRepositoryInterface $missionRepository,
        private AwayMissionService $missionService
    ) {}

    public function getMission(Spacecraft $ship, InformationInterface $information): ?AwayMission
    {
        $colony = $ship->getStarsystemMap()?->getColony();
        $mission = $colony === null ? null : $this->missionRepository->getByColony($colony);
        if ($mission === null || !$this->missionService->isAvailable($mission)) {
            $information->addInformation('An dieser Position ist keine Außenmission verfügbar');
            return null;
        }

        if ($ship->isCloaked() || $ship->getCondition()->isDisabled()
            || !$ship->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER)) {
            $information->addInformation('Für die Außenmission wird ein einsatzfähiges, ungetarntes Schiff mit funktionsfähigen Matrixsensoren benötigt');
            return null;
        }

        return $mission;
    }
}
