<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DoTachyonScan;

use request;

use Stu\Component\Ship\System\Type\TachyonScannerShipSystem;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\TachyonScanRepositoryInterface;

final class DoTachyonScan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TACHYON_SCAN';

    private ShipLoaderInterface $shipLoader;

    private TachyonScanRepositoryInterface $tachyonScanRepository;

    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TachyonScanRepositoryInterface $tachyonScanRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->tachyonScanRepository = $tachyonScanRepository;
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        // scanner needs to be present
        if (!$ship->hasTachyonScanner())
        {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, kein Tachyon-Scanner installiert![/color][/b]'));
            return;
        }

        // scanner needs to be active
        if (!$ship->getTachyonState())
        {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, der Tachyon-Scanner muss aktiviert sein![/color][/b]'));
            return;
        }

        // scanner needs to be active
        if ($ship->getEps() < TachyonScannerShipSystem::SCAN_EPS_COST)
        {
            $game->addInformation(sprintf(_('[b][color=FF2626]Aktion nicht möglich, ungenügend Energie vorhanden. Bedarf: %dE[/color][/b]'), TachyonScannerShipSystem::SCAN_EPS_COST));
            return;
        }

        $tachyonScan = $this->tachyonScanRepository->prototype();
        $tachyonScan->setUser($ship->getUser());

        if ($ship->getSystem() === null)
        {
            $tachyonScan->setMap($this->mapRepository->getByCoordinates($ship->getPosX(), $ship->getPosY()));
        }
        else {
            $tachyonScan->setStarsystemMap($this->starSystemMapRepository->
                            getByCoordinates($ship->getSystem()->getId(), $ship->getPosX(), $ship->getPosY()));
        }

        $tachyonScan->setScanTime(time());
        $this->tachyonScanRepository->save($tachyonScan);

        $ship->setEps($ship->getEps() - TachyonScannerShipSystem::SCAN_EPS_COST);
        $this->shipRepository->save($ship);
        
        $game->setView(ShowShip::VIEW_IDENTIFIER, ['TACHYON_SCAN_JUST_HAPPENED' => true]);
        $game->addInformation("Der umfangreiche Tachyon-Scan wurde durchgeführt");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
