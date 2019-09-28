<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSectorScan;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
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

        $planetType = $mapField->getFieldType()->getPlanetType();
        if ($planetType !== null) {
            $game->checkDatabaseItem($planetType->getDatabaseId());
        }
        if ($mapField->getFieldType()->getIsSystem()) {
            $game->checkDatabaseItem($ship->getCurrentMapField()->getSystem()->getSystemType()->getDatabaseEntryId());
        }
        if ($ship->getSystem() !== null) {
            $databaseEntry = $ship->getSystem()->getDatabaseEntry();
            if ($databaseEntry !== null) {
                $game->checkDatabaseItem($databaseEntry->getId());
            }
        }

        $game->setTemplateVar('SHIP', $ship);
    }
}
