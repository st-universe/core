<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowSensorScan;

use request;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowSensorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SENSOR_SCAN';

    public const ENERGY_COST_SECTOR_SCAN = 15;

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private MapRepositoryInterface $mapRepository;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private NbsUtilityInterface $nbsUtility;

    private $fadedSignaturesUncloaked = [];
    private $fadedSignaturesCloaked = [];

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        MapRepositoryInterface $mapRepository,
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        NbsUtilityInterface $nbsUtility
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->mapRepository = $mapRepository;
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->nbsUtility = $nbsUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $cx = request::getIntFatal('cx');
        $cy = request::getIntFatal('cy');
        $mapField = $this->mapRepository->getByCoordinates($cx, $cy);


        $game->setPageTitle("Sensor Scan");
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/stationmacros.xhtml/sensorscan');

        $game->setTemplateVar('ERROR', true);

        if ($mapField === null) {
            return;
        }

        if (!$ship->getLss()) {
            return;
        }

        if ($ship->getEps() < self::ENERGY_COST_SECTOR_SCAN) {
            $game->addInformation(sprintf(_('Nicht genügend Energie vorhanden (%d benötigt)'), self::ENERGY_COST_SECTOR_SCAN));
            return;
        }

        $ship->setEps($ship->getEps() - self::ENERGY_COST_SECTOR_SCAN);
        $this->shipRepository->save($ship);

        $tachyonActive = $this->nbsUtility->isTachyonActive($ship);
        $this->nbsUtility->setNbsTemplateVars($ship, $game, null, $tachyonActive, $mapField->getId());


        $game->setTemplateVar('MAPFIELD', $mapField);
        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField, $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', empty($this->fadedSignaturesUncloaked) ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', empty($this->fadedSignaturesCloaked) ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('ERROR', false);
    }

    private function getSignatures($field, $ignoreId)
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($field, false, $ignoreId);

        $filteredSigs = [];

        foreach ($allSigs as $sig) {
            $id = $sig->getShip()->getId();

            if (!array_key_exists($id, $filteredSigs)) {
                $wrapper = new SignatureWrapper($sig);

                if ($wrapper->getRump() == null) {
                    if ($sig->isCloaked()) {
                        if ($sig->getTime() > (time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_CLOAKED)) {
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
