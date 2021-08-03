<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Nbs;

use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\FleetNfsIterator;
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
        return !empty($this->tachyonScanRepository->findActiveByShipLocationAndOwner($ship));
    }

    public function setNbsTemplateVars(
        ShipInterface $ship,
        GameControllerInterface $game,
        ?SessionInterface $session,
        bool $tachyonActive,
        int $mapId = null
    ): void {
        $stationNbs = new ShipNfsIterator($this->shipRepository->getSingleShipScannerResults(
            $ship,
            true,
            $tachyonActive,
            $mapId
        ), $ship->getUser()->getId());

        $singleShipsNbs = new ShipNfsIterator($this->shipRepository->getSingleShipScannerResults(
            $ship,
            false,
            $tachyonActive,
            $mapId
        ), $ship->getUser()->getId());

        $fleetNbs = new FleetNfsIterator(
            $this->shipRepository->getFleetShipsScannerResults($ship, $tachyonActive),
            $ship,
            $session
        );

        $game->setTemplateVar(
            'HAS_NBS',
            $fleetNbs->count() > 0 || $stationNbs->count() > 0 || $singleShipsNbs->count() > 0
        );

        $game->setTemplateVar('CLOAK_NBS', !$tachyonActive && $ship->getTachyonState() && $this->shipRepository->isCloakedShipAtLocation($ship));
        $game->setTemplateVar('FLEET_NBS', $fleetNbs);
        $game->setTemplateVar('STATION_NBS', $stationNbs->count() > 0 ? $stationNbs : null);
        $game->setTemplateVar('SHIP_NBS', $singleShipsNbs->count() > 0 ? $singleShipsNbs : null);
    }
}
