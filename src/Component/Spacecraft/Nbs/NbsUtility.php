<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Nbs;

use Override;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Fleet\FleetNfsIterator;
use Stu\Module\Spacecraft\Lib\SpacecraftNfsIterator;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\TachyonScanRepositoryInterface;

final class NbsUtility implements NbsUtilityInterface
{
    public function __construct(
        private TachyonScanRepositoryInterface $tachyonScanRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ShipRepositoryInterface $shipRepository,
        private StationRepositoryInterface $stationRepository
    ) {}

    #[Override]
    public function isTachyonActive(Spacecraft $spacecraft): bool
    {
        return $this->tachyonScanRepository->isTachyonScanActiveByShipLocationAndOwner($spacecraft);
    }

    #[Override]
    public function setNbsTemplateVars(
        Spacecraft $spacecraft,
        GameControllerInterface $game,
        ?SessionStorageInterface $sessionStorage,
        bool $tachyonActive,
        Map|StarSystemMap|null $field = null
    ): void {
        if ($spacecraft->getNbs() || $field !== null) {
            $stationNbs = new SpacecraftNfsIterator($this->stationRepository->getStationScannerResults(
                $spacecraft,
                $tachyonActive,
                $field
            ), $game->getUser()->getId());

            $singleSpacecraftsNbs = new SpacecraftNfsIterator($this->spacecraftRepository->getSingleSpacecraftScannerResults(
                $spacecraft,
                $tachyonActive,
                $field
            ), $game->getUser()->getId());

            $fleetNbs = new FleetNfsIterator(
                $this->shipRepository->getFleetShipsScannerResults(
                    $spacecraft,
                    $tachyonActive,
                    $field
                ),
                $spacecraft,
                $sessionStorage,
                $game->getUser()->getId()
            );

            $trumfieldNbs = $field !== null
                ? $field->getTrumfields()->toArray()
                : $spacecraft->getLocation()->getTrumfields()->toArray();

            $game->setTemplateVar(
                'HAS_NBS',
                $fleetNbs->count() > 0 || $stationNbs->count() > 0 || $singleSpacecraftsNbs->count() > 0 || count($trumfieldNbs) > 0
            );

            $game->setTemplateVar('CLOAK_NBS', $this->showCloakedShipInfo($spacecraft, $tachyonActive));
            $game->setTemplateVar('FLEET_NBS', $fleetNbs);
            $game->setTemplateVar('STATION_NBS', $stationNbs->count() > 0 ? $stationNbs : null);
            $game->setTemplateVar('SPACECRAFT_NBS', $singleSpacecraftsNbs->count() > 0 ? $singleSpacecraftsNbs : null);
            $game->setTemplateVar('TRUMFIELD_NBS', $trumfieldNbs);
        }
    }

    private function showCloakedShipInfo(Spacecraft $spacecraft, bool $tachyonActive): bool
    {
        return !$tachyonActive
            && $spacecraft->getTachyonState()
            && $this->spacecraftRepository->isCloakedSpacecraftAtLocation($spacecraft);
    }
}
