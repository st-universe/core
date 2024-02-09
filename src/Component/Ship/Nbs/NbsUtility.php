<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Nbs;

use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Fleet\FleetNfsIterator;
use Stu\Module\Ship\Lib\ShipNfsIterator;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TachyonScanRepositoryInterface;

final class NbsUtility implements NbsUtilityInterface
{
    private TachyonScanRepositoryInterface $tachyonScanRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        TachyonScanRepositoryInterface $tachyonScanRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->tachyonScanRepository = $tachyonScanRepository;
        $this->shipRepository = $shipRepository;
    }

    public function isTachyonActive(ShipInterface $ship): bool
    {
        return $this->tachyonScanRepository->isTachyonScanActiveByShipLocationAndOwner($ship);
    }

    public function setNbsTemplateVars(
        ShipInterface $ship,
        GameControllerInterface $game,
        ?SessionInterface $session,
        bool $tachyonActive,
        int $mapId = null,
        int $sysMapId = null
    ): void {
        if ($ship->getNbs() || $mapId !== null || $sysMapId !== null) {
            $stationNbs = new ShipNfsIterator($this->shipRepository->getSingleShipScannerResults(
                $ship,
                [SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION->value],
                $tachyonActive,
                $mapId,
                $sysMapId
            ), $game->getUser()->getId());

            $singleShipsNbs = new ShipNfsIterator($this->shipRepository->getSingleShipScannerResults(
                $ship,
                [SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP->value, SpacecraftTypeEnum::SPACECRAFT_TYPE_OTHER->value],
                $tachyonActive,
                $mapId,
                $sysMapId
            ), $game->getUser()->getId());

            $fleetNbs = new FleetNfsIterator(
                $this->shipRepository->getFleetShipsScannerResults(
                    $ship,
                    $tachyonActive,
                    $mapId,
                    $sysMapId
                ),
                $ship,
                $session,
                $game->getUser()->getId()
            );

            $game->setTemplateVar(
                'HAS_NBS',
                $fleetNbs->count() > 0 || $stationNbs->count() > 0 || $singleShipsNbs->count() > 0
            );

            $game->setTemplateVar('CLOAK_NBS', $this->showCloakedShipInfo($ship, $tachyonActive));
            $game->setTemplateVar('FLEET_NBS', $fleetNbs);
            $game->setTemplateVar('STATION_NBS', $stationNbs->count() > 0 ? $stationNbs : null);
            $game->setTemplateVar('SHIP_NBS', $singleShipsNbs->count() > 0 ? $singleShipsNbs : null);
        } else {
            $game->setTemplateVar('HAS_NBS', false);
            $game->setTemplateVar('CLOAK_NBS', false);
            $game->setTemplateVar('FLEET_NBS', []);
            $game->setTemplateVar('STATION_NBS', null);
            $game->setTemplateVar('SHIP_NBS', null);
        }
    }

    private function showCloakedShipInfo(ShipInterface $ship, bool $tachyonActive): bool
    {
        return !$tachyonActive
            && $ship->getTachyonState()
            && $this->shipRepository->isCloakedShipAtLocation(
                $ship->getStarsystemMap() !== null ? $ship->getStarsystemMap()->getId() : null,
                $ship->getMap() !== null ? $ship->getMap()->getId() : null,
                $ship->getUser()->getId()
            );
    }
}
