<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSectorScan;

use request;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class ShowSectorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SECTOR_SCAN';

    private ShipLoaderInterface $shipLoader;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->flightSignatureRepository = $flightSignatureRepository;
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

        //6 stunden name des schiffes
        //12 stunden rumpf
        //2 tage anzahl

        //getarnte rümpfe 6 stunden
        //getarnte sigs 12 stunden

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField, $ship->getSystem() !== null, $userId));
        $game->setTemplateVar('SHIP', $ship);
    }

    private function getSignatures($field, $isSystem, $ignoreId)
    {
        return array_map(
            function (array $data): SignatureWrapper {
                return new SignatureWrapper($data);
            },
            $this->flightSignatureRepository->getVisibleSignatures($field, $isSystem, $ignoreId)
        );
    }
}
