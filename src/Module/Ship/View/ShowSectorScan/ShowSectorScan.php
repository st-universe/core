<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSectorScan;

use request;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\BuoyRepositoryInterface;

final class ShowSectorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SECTOR_SCAN';

    private ShipLoaderInterface $shipLoader;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private EncodedMapInterface $encodedMap;

    private BuoyRepositoryInterface $buoyRepository;

    /** @var array<int> */
    private array $fadedSignaturesUncloaked = [];

    /** @var array<int> */
    private array $fadedSignaturesCloaked = [];

    public function __construct(
        ShipLoaderInterface $shipLoader,
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        EncodedMapInterface $encodedMap,
        BuoyRepositoryInterface $buoyRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->encodedMap = $encodedMap;
        $this->buoyRepository = $buoyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setPageTitle("Sektor Scan");
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/sectorscan');
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

        $mapField = $ship->getCurrentMapField();

        $colonyClass = $mapField->getFieldType()->getColonyClass();
        if ($colonyClass !== null) {
            $game->checkDatabaseItem($colonyClass->getDatabaseId());
        }
        if ($mapField->getSystem() !== null && $mapField->getFieldType()->getIsSystem()) {
            $game->checkDatabaseItem($mapField->getSystem()->getSystemType()->getDatabaseEntryId());
        }
        if ($ship->getStarsystemMap() !== null) {
            $buoy = $this->buoyRepository->findBySysMapId($ship->getStarsystemMap()->getId());
        }
        if ($ship->getMap() !== null) {
            $buoy = $this->buoyRepository->findByMapId($ship->getMap()->getId());
        }

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField->getId(), $ship->getSystem() !== null, $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', empty($this->fadedSignaturesUncloaked) ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', empty($this->fadedSignaturesCloaked) ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('MAP_PATH', $this->getMapPath($ship));
        $game->setTemplateVar('BUOY', $buoy);
        $game->setTemplateVar('ERROR', false);
    }

    /**
     * @return array<int, SignatureWrapper>
     */
    private function getSignatures(int $fieldId, bool $isSystem, int $ignoreId): array
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($fieldId, $isSystem, $ignoreId);

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
        $currentMapField = $ship->getCurrentMapField();

        if ($currentMapField instanceof MapInterface) {
            return $this->encodedMap->getEncodedMapPath($currentMapField->getFieldId(), $currentMapField->getLayer());
        } else {
            return sprintf('%d.png', $currentMapField->getFieldId());
        }
    }
}