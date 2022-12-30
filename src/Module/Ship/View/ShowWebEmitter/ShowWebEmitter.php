<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowWebEmitter;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowWebEmitter implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_WEBEMITTER_AJAX';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle(_('Webemitter'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/webemitter');

        $game->setTemplateVar('WRAPPER', $wrapper);
    }
}
