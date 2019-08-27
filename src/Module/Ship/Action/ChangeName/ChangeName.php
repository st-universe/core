<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeName;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class ChangeName implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_NAME';

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

        $value = request::postString('shipname');
        $ship->setName(tidyString($value));
        $ship->save();
        $game->addInformation("Der Schiffname wurde geändert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
