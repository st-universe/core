<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSectorScan;

use Override;
use request;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class ShowSectorScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SECTOR_SCAN';

    /** @var array<int> */
    private array $fadedSignaturesUncloaked = [];

    /** @var array<int> */
    private array $fadedSignaturesCloaked = [];

    public function __construct(private ShipLoaderInterface $shipLoader, private FlightSignatureRepositoryInterface $flightSignatureRepository, private EncodedMapInterface $encodedMap) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setPageTitle("Sektor Scan");
        $game->setMacroInAjaxWindow('html/ship/sectorscan.twig');
        $game->setTemplateVar('ERROR', true);

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );
        $ship = $wrapper->get();

        if (!$ship->getNbs()) {
            $game->addInformation("Die Nahbereichssensoren sind nicht aktiv");
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->addInformation("Nicht genügend Energie vorhanden (1 benötigt)");
            return;
        }

        $epsSystem->lowerEps(1)->update();

        $mapField = $ship->getLocation();

        $colonyClass = $mapField->getFieldType()->getColonyClass();
        if ($colonyClass !== null) {
            $game->checkDatabaseItem($colonyClass->getDatabaseId());
        }
        $this->checkDatabaseItemForMap($mapField, $game);

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField->getId(), $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', $this->fadedSignaturesUncloaked === [] ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', $this->fadedSignaturesCloaked === [] ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('MAP_PATH', $this->getMapPath($ship));
        $game->setTemplateVar('BUOYS', $ship->getLocation()->getBuoys());

        $game->setTemplateVar('ERROR', false);
    }

    private function checkDatabaseItemForMap(LocationInterface $location, GameControllerInterface $game): void
    {
        if (!$location instanceof MapInterface) {
            return;
        }

        $system = $location->getSystem();
        if (
            $system !== null
            && $location->getFieldType()->getIsSystem()
        ) {
            $game->checkDatabaseItem($system->getSystemType()->getDatabaseEntryId());
        }
    }

    /**
     * @return array<int, SignatureWrapper>
     */
    private function getSignatures(int $fieldId, int $ignoreId): array
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($fieldId, $ignoreId);

        $filteredSigs = [];

        foreach ($allSigs as $sig) {
            $id = $sig->getShipId();
            $name = $sig->getShipName();

            if (!array_key_exists($id . '_' . $name, $filteredSigs)) {
                $wrapper = new SignatureWrapper($sig);

                if ($wrapper->getRump() === null) {
                    if ($sig->isCloaked()) {
                        if ($sig->getTime() > (time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_CLOAKED)) {
                            $this->fadedSignaturesCloaked[$id] = $id;
                        }
                    } else {
                        $this->fadedSignaturesUncloaked[$id] = $id;
                    }
                } else {
                    $filteredSigs[$id . '_' . $name] = $wrapper;
                }
            }
        }

        return $filteredSigs;
    }

    private function getMapPath(ShipInterface $ship): string
    {
        $currentMapField = $ship->getLocation();
        $layer = $currentMapField->getLayer();

        if ($currentMapField instanceof MapInterface && $layer !== null) {
            return $this->encodedMap->getEncodedMapPath($currentMapField->getFieldId(), $layer);
        } else {
            return sprintf('%d.png', $currentMapField->getFieldId());
        }
    }
}
