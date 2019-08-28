<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSectorScan;

use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowSectorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SECTOR_SCAN';

    private $shipLoader;

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
            $userId
        );

        $game->setPageTitle("Sektor Scan");
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/sectorscan');

        $mapField = $ship->getCurrentMapField();

        if ($mapField->getFieldType()->getColonyClass()) {
            $game->checkDatabaseItem($mapField->getFieldType()->getColonyType()->getDatabaseId());
        }
        if ($mapField->getFieldType()->getIsSystem()) {
            $game->checkDatabaseItem($ship->getCurrentMapField()->getSystem()->getSystemType()->getDatabaseId());
        }
        if ($ship->isInSystem()) {
            $game->checkDatabaseItem($ship->getSystem()->getDatabaseId());
        }

        $game->setTemplateVar('SHIP', $ship);
    }
}
