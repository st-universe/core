<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivatePhaser;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class ActivatePhaser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_PHASER';

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
        if (!$ship->hasPhaser() || $ship->phaserIsActive()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship->setPhaser(1);
        $ship->save();
        $game->addInformation("Strahlenwaffe aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
