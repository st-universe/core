<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowSensorScan;

use request;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Component\Map\EncodedMapInterface;

final class ShowSensorScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SENSOR_SCAN';

    public const int ENERGY_COST_SECTOR_SCAN = 15;

    private LoggerUtilInterface $loggerUtil;

    /** @var array<int> */
    private array $fadedSignaturesUncloaked = [];

    /** @var array<int> */
    private array $fadedSignaturesCloaked = [];

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private MapRepositoryInterface $mapRepository,
        private StarSystemMapRepositoryInterface $starSystemMapRepository,
        private FlightSignatureRepositoryInterface $flightSignatureRepository,
        private NbsUtilityInterface $nbsUtility,
        private EncodedMapInterface $encodedMap,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        //$this->loggerUtil->init('stu', LogLevelEnum::ERROR);

        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );
        $station = $wrapper->get();

        $cx = request::getIntFatal('x');
        $cy = request::getIntFatal('y');

        if (!$station->getLss()) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < self::ENERGY_COST_SECTOR_SCAN) {
            $game->getInfo()->addInformation(sprintf(_('Nicht genügend Energie vorhanden (%d benötigt)'), self::ENERGY_COST_SECTOR_SCAN));
            $game->setPageTitle(sprintf(_('Sensor Scan %d|%d fehlgeschlagen'), $cx, $cy));
            $game->setMacroInAjaxWindow('');
            return;
        }

        $sysid = request::getIntFatal('systemid');

        $this->loggerUtil->log(sprintf('cx: %d, cy: %d, sysid: %d', $cx, $cy, $sysid));

        $field = $station->getLocation();
        $stationCx = $field->getCx();
        $stationCy = $field->getCy();

        $sensorRange = $wrapper->getLssSystemData()?->getSensorRange() ?? 0;

        if ($sysid === 0) {

            if (
                $cx < $stationCx - $sensorRange
                || $cx > $stationCx + $sensorRange
                || $cy < $stationCy - $sensorRange
                || $cy > $stationCy + $sensorRange
            ) {
                return;
            }

            $mapField = $this->mapRepository->getByCoordinates($station->getLayer(), $cx, $cy);
        } else {
            $mapField = $this->starSystemMapRepository->getByCoordinates($sysid, $cx, $cy);
            if ($mapField === null) {
                return;
            }

            $system = $mapField->getSystem();

            if (
                $system->getCx() < $stationCx - $sensorRange
                || $system->getCx() > $stationCx + $sensorRange
                || $system->getCy() < $stationCy - $sensorRange
                || $system->getCy() > $stationCy + $sensorRange
            ) {
                return;
            }
        }

        if ($mapField === null) {
            return;
        }

        if ($station->getLayer() !== null) {
            $encodedMapPath = $this->encodedMap->getEncodedMapPath(
                $mapField->getFieldType()->getId(),
                $station->getLayer()
            );

            $game->setTemplateVar('MAPFIELDPATH', $encodedMapPath);
        } else {
            $game->setTemplateVar('MAPFIELDPATH', "1.png");
        }


        $game->setPageTitle(sprintf(_('Sensor Scan %d|%d'), $cx, $cy));
        $game->setTemplateFile('html/station/sensorScan.twig');

        $epsSystem->lowerEps(self::ENERGY_COST_SECTOR_SCAN)->update();
        $this->stationLoader->save($station);

        //$tachyonActive = $this->nbsUtility->isTachyonActive($ship);
        $tachyonActive = $station->getSystemState(SpacecraftSystemTypeEnum::TACHYON_SCANNER);

        if ($sysid !== 0) {
            $this->loggerUtil->log('system!');
            $game->setTemplateVar('SYSTEM_INTERN', true);
        } else {
            $this->loggerUtil->log('not:system!');
        }

        $this->nbsUtility->setNbsTemplateVars($station, $game, null, $tachyonActive, $mapField);

        $game->setTemplateVar('MAPFIELD', $mapField);
        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField->getId(), $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', $this->fadedSignaturesUncloaked === [] ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', $this->fadedSignaturesCloaked === [] ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('WRAPPER', $wrapper);
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
