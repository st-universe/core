<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateWarp;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;
use SystemActivationWrapper;

final class ActivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_WARP';

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

        if ($ship->getWarpState()) {
            return;
        }
        if (!$ship->isWarpAble()) {
            return;
        }
        // @todo arpantrieb beschädigt
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        if ($ship->getDock()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDock(0);
        }
        if ($ship->traktorBeamFromShip()) {
            if ($ship->getEps() == 0) {
                $game->addInformation("Der Traktorstrahl zur " . $ship->getTraktorShip()->getName() . " wurde aufgrund von Energiemangel deaktiviert");
                $ship->getTraktorShip()->unsetTraktor();

                $this->shipRepository->save($ship->getTraktorShip());

                $ship->unsetTraktor();
            } else {
                $ship->getTraktorShip()->setWarpState(true);

                $this->shipRepository->save($ship->getTraktorShip());

                $ship->setEps($ship->getEps() - 1);
            }
        }
        $ship->setWarpState(true);

        $this->shipRepository->save($ship);

        $game->addInformation("Die " . $ship->getName() . " hat den Warpantrieb aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
