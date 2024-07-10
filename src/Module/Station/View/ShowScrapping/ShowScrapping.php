<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowScrapping;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowScrapping implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SCRAP_AJAX';

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
            false,
            false
        );

        $code = substr(md5($ship->getName()), 0, 6);

        $game->setPageTitle(_('Demontage'));
        $game->setMacroInAjaxWindow('html/ship/scrapping.twig');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('SCRAP_CODE', $code);
    }
}
