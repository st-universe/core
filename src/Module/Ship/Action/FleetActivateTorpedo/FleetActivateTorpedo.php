<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateTorpedo;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetActivateTorpedo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_TORPEDO';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
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

        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Torpedobänke";
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if (!$ship->hasTorpedo() || $ship->getTorpedos()) {
                continue;
            }
            if (!$ship->getTorpedoCount()) {
                $msg[] = $ship->getName() . _(": Keine Torpedos geladen");
                continue;
            }
            if ($ship->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_TORPEDO) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            $ship->setEps($ship->getEps() - ShipSystemTypeEnum::SYSTEM_ECOST_TORPEDO);
            $ship->setTorpedos(true);

            $this->shipRepository->save($ship);
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
