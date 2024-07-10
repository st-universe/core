<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShipStorage;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowShipStorage implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIPSTORAGE';

    public function __construct(private ShipLoaderInterface $shipLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle(_('Schiffsfracht'));
        $game->setMacroInAjaxWindow('html/ship/shipstorage.twig');

        $game->setTemplateVar('SHIP', $ship);
    }
}
