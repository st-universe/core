<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowSensorScan;

use request;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShowSensorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SENSOR_SCAN';

    public const ENERGY_COST_SECTOR_SCAN = 15;

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private NbsUtilityInterface $nbsUtility;

    private LoggerUtilInterface $loggerUtil;

    /** @var array<int> */
    private array $fadedSignaturesUncloaked = [];

    /** @var array<int> */
    private array $fadedSignaturesCloaked = [];

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        NbsUtilityInterface $nbsUtility,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->nbsUtility = $nbsUtility;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );
        $station = $wrapper->get();

        $game->setTemplateVar('ERROR', true);

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < self::ENERGY_COST_SECTOR_SCAN) {
            $game->addInformation(sprintf(_('Nicht genügend Energie vorhanden (%d benötigt)'), self::ENERGY_COST_SECTOR_SCAN));
            return;
        }

        $cx = request::getIntFatal('cx');
        $cy = request::getIntFatal('cy');
        $sysid = request::getIntFatal('sysid');

        $this->loggerUtil->log(sprintf('cx: %d, cy: %d, sysid: %d', $cx, $cy, $sysid));


        if ($sysid === 0) {
            if (
                $cx < $station->getCx() - $station->getSensorRange()
                || $cx > $station->getCx() + $station->getSensorRange()
                || $cy < $station->getCy() - $station->getSensorRange()
                || $cy > $station->getCy() + $station->getSensorRange()
            ) {
                return;
            }

            $mapField = $this->mapRepository->getByCoordinates($station->getLayerId(), $cx, $cy);
        } else {
            $mapField = $this->starSystemMapRepository->getByCoordinates($sysid, $cx, $cy);
            if ($mapField === null) {
                return;
            }

            $system = $mapField->getSystem();

            if (
                $system->getCx() < $station->getCx() - $station->getSensorRange()
                || $system->getCx() > $station->getCx() + $station->getSensorRange()
                || $system->getCy() < $station->getCy() - $station->getSensorRange()
                || $system->getCy() > $station->getCy() + $station->getSensorRange()
            ) {
                return;
            }
        }

        $game->setPageTitle(sprintf(_('Sensor Scan %d|%d'), $cx, $cy));
        $game->setTemplateFile('html/station/sensorScan.twig');

        if ($mapField === null) {
            return;
        }

        if (!$station->getLss()) {
            return;
        }

        $epsSystem->lowerEps(self::ENERGY_COST_SECTOR_SCAN)->update();
        $this->shipRepository->save($station);

        //$tachyonActive = $this->nbsUtility->isTachyonActive($ship);
        $tachyonActive = $station->getSystemState(ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER);

        if ($sysid !== 0) {
            $this->loggerUtil->log('system!');
            $game->setTemplateVar('SYSTEM_INTERN', true);
            $this->nbsUtility->setNbsTemplateVars($station, $game, null, $tachyonActive, null, $mapField->getId());
        } else {
            $this->loggerUtil->log('not:system!');
            $this->nbsUtility->setNbsTemplateVars($station, $game, null, $tachyonActive, $mapField->getId(), null);
        }

        $game->setTemplateVar('MAPFIELD', $mapField);
        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField->getId(), $userId, $sysid !== 0));
        $game->setTemplateVar('OTHER_SIG_COUNT', empty($this->fadedSignaturesUncloaked) ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', empty($this->fadedSignaturesCloaked) ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('WRAPPER', $wrapper);
        $game->setTemplateVar('ERROR', false);
    }

    /**
     * @return array<int, SignatureWrapper>
     */
    private function getSignatures(int $fieldId, int $ignoreId, bool $isSystem): array
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($fieldId, $isSystem, $ignoreId);

        $filteredSigs = [];

        foreach ($allSigs as $sig) {
            $id = $sig->getShipId();

            if (!array_key_exists($id, $filteredSigs)) {
                $wrapper = new SignatureWrapper($sig);

                if ($wrapper->getRump() == null) {
                    if ($sig->isCloaked()) {
                        if ($sig->getTime() > (time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_CLOAKED)) {
                            $this->fadedSignaturesCloaked[] = $id;
                        }
                    } else {
                        $this->fadedSignaturesUncloaked[] = $id;
                    }
                } else {
                    $filteredSigs[$id] = $wrapper;
                }
            }
        }

        return $filteredSigs;
    }
}
