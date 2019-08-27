<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LoadWarpcoreMax;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class LoadWarpcoreMax implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOAD_WARPCORE_MAX';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (request::postString('fleet')) {
            $msg = array();
            $msg[] = _('Flottenbefehl ausgeführt: Aufladung des Warpkerns');
            DB()->beginTransaction();
            foreach ($ship->getFleet()->getShips() as $key => $ship) {
                if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
                    continue;
                }
                $load = $ship->loadWarpCore(ceil(($this->getShip()->getWarpcoreCapacity() - $this->getShip()->getWarpcoreLoad()) / WARPCORE_LOAD));
                if (!$load) {
                    $game->addInformation(sprintf(_('%s: Zum Aufladen des Warpkerns werden mindestens 1 Deuterium sowie 1 Antimaterie benötigt'),
                        $ship->getName()));
                    continue;
                }
                $game->addInformation(sprintf(_('%s: Der Warpkern wurde um %d Einheiten aufgeladen'), $ship->getName(),
                    $load));
            }
            DB()->commitTransaction();
            $game->addInformationMerge($msg);
            return;
        }
        if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
            $game->addInformation(_('Der Warpkern ist bereits vollständig geladen'));
            return;
        }
        $load = $ship->loadWarpCore(ceil(($this->getShip()->getWarpcoreCapacity() - $this->getShip()->getWarpcoreLoad()) / WARPCORE_LOAD));
        if (!$load) {
            $game->addInformation(_('Zum Aufladen des Warpkerns werden mindestens 1 Deuterium sowie 1 Antimaterie benötigt'));
            return;
        }
        $game->addInformation(sprintf(_('Der Warpkern wurde um %d Einheiten aufgeladen'), $load));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
