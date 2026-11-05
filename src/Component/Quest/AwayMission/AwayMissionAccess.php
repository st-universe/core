<?php

declare(strict_types=1);

namespace Stu\Component\Quest\AwayMission;

use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;

final class AwayMissionAccess
{
    public function __construct(
        private AwayMissionRepositoryInterface $missionRepository,
        private AwayMissionService $missionService
    ) {}

    public function getAvailableMission(Spacecraft $ship): ?AwayMission
    {
        if (!$ship instanceof Ship) {
            return null;
        }
        $colony = $ship->getStarsystemMap()?->getColony();
        $mission = $colony === null ? null : $this->missionRepository->getByColony($colony);
        return $mission !== null && $this->missionService->isAvailable($mission) ? $mission : null;
    }

    public function getMission(Spacecraft $ship, InformationInterface $information): ?AwayMission
    {
        $mission = $this->getAvailableMission($ship);
        if ($mission === null) {
            $information->addInformation('An dieser Position ist keine Außenmission verfügbar');
            return null;
        }

        if ($ship->isCloaked() || $ship->getCondition()->isDisabled()) {
            $information->addInformation('Für die Außenmission wird ein einsatzfähiges, ungetarntes Schiff benötigt');
            return null;
        }

        return $mission;
    }
}
