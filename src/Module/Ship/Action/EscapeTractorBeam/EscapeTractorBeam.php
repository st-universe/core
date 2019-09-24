<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EscapeTractorBeam;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class EscapeTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ESCAPE_TRAKTOR';

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

        $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        // @todo implement
        $game->addInformation('Nicht implementiert');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
