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

    private $fadedSignaturesUncloaked = [];
    private $fadedSignaturesCloaked = [];

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

        $game->setTemplateVar('ERROR', true);

        if (!$ship->getNbs()) {
            $game->addInformation("Die Nahbereichssensoren sind nicht aktiv");
            return;
        }

        if ($ship->getEps() < 1) {
            $game->addInformation("Nicht genügend Energie vorhanden (1 benötigt)");
            return;
        }

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

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField, $ship->getSystem() !== null, $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', empty($this->fadedSignaturesUncloaked) ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', empty($this->fadedSignaturesCloaked) ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('ERROR', false);
    }

    private function getSignatures($field, $isSystem, $ignoreId)
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($field, $isSystem, $ignoreId);

        $filteredSigs = [];

        foreach ($allSigs as $sig) {
            $id = $sig->getShip()->getId();

            if (!array_key_exists($id, $filteredSigs)) {
                $wrapper = new SignatureWrapper($sig);

                if ($wrapper->getRump() == null) {
                    if ($sig->isCloaked()) {
                        if ($sig->getTime() > (time() - 43200)) {
                            $this->fadedSignaturesCloaked[$id] = $id;
                        }
                    } else {
                        $this->fadedSignaturesUncloaked[$id] = $id;
                    }
                } else {
                    $filteredSigs[$id] = $wrapper;
                }
            }
        }

        return $filteredSigs;
    }
}
