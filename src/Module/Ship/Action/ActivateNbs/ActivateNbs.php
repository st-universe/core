<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateNbs;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class ActivateNbs implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_NBS';

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
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship->setNbs(1);
        $ship->save();
        $game->addInformation("Nahbereichssensoren aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
