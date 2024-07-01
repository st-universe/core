<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRenameCrew;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowRenameCrew implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_RENAME_CREW';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $game->showMacro('html/ship/crew/crewSlot.twig');

        $game->setTemplateVar('SHIP', $ship);
    }
}
