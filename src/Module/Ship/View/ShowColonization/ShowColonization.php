<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowColonization;

use Colony;
use request;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowColonization implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONIZATION';

    private $shipLoader;

    private $colonyLibFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colonyId = request::getIntFatal('colid');
        $colony = new Colony($colonyId);
        // @todo add sanity checks

        $game->setPageTitle(_('Kolonie gründen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/colonization');

        $game->setTemplateVar('currentColony', $colony);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
    }
}
