<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use Stu\Component\Spacecraft\System\Data\SubspaceSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemWrapperFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

class SubspaceSensorSystemSettings implements SystemSettingsProviderInterface
{
    public function __construct(
        private readonly SpacecraftSystemWrapperFactoryInterface $spacecraftSystemWrapperFactory,
        private readonly FlightSignatureRepositoryInterface $flightSignatureRepository,
        private readonly StuTime $stuTime
    ) {}

    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {
        $game->setMacroInAjaxWindow('html/spacecraft/system/subspaceScanner.twig');

        $spacecraft = $wrapper->get();

        $isSubspaceScannerActive = $spacecraft->getSystemState(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);
        $isMatrixScannerHealthy = $spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER);

        if ($isMatrixScannerHealthy && $isSubspaceScannerActive) {

            $subspaceSystemData = $wrapper->getSubspaceSystemData();
            if ($subspaceSystemData === null) {
                return;
            }

            $time = $this->stuTime->time();

            $this->setAnalyzedSignature($subspaceSystemData, $game);
            $this->setAnalyzeTime($time, $subspaceSystemData, $game);
            $this->setSignatures($wrapper, $time, $game);
        } else {
            $game->setTemplateVar('SYSTEMWARNING', true);
        }

        $game->setTemplateVar('USER', $game->getUser());
        $game->setTemplateVar('SPACECRAFT', $spacecraft);
        $game->setTemplateVar(
            'systemWrapper',
            $this->spacecraftSystemWrapperFactory->create($wrapper->get(), $systemType)
        );
    }

    private function setAnalyzedSignature(SubspaceSystemData $subspaceSystemData, GameControllerInterface $game): void
    {
        $flightSigId = $subspaceSystemData->getFlightSigId();
        if ($flightSigId) {
            $flightSig = $this->flightSignatureRepository->find($flightSigId);
            $game->setTemplateVar('ANALYZED_SIGNATURE', $flightSig);
        }
    }

    private function setAnalyzeTime(int $time, SubspaceSystemData $subspaceSystemData, GameControllerInterface $game): void
    {
        $analyzeTime = $subspaceSystemData->getAnalyzeTime();
        if ($analyzeTime) {
            $currentTime = $time;
            $maxTime = $analyzeTime + (10 * 60);
            $game->setTemplateVar('ANALYZE_TIME', $analyzeTime);

            if ($currentTime <= $maxTime) {
                $game->setTemplateVar('ANALYZE_TIME', $analyzeTime);
            }
        }
    }

    private function setSignatures(SpacecraftWrapperInterface $wrapper, int $time, GameControllerInterface $game): void
    {
        $spacecraft = $wrapper->get();
        $location = $spacecraft->getLocation();

        $layerId = $location->getLayer()?->getId();
        if ($layerId === null) {
            return;
        }

        $system = $spacecraft->getSystem();
        if ($system) {
            $cx = $system->getCx();
            $cy = $system->getCy();
        } else {
            $cx = $location->getCx();
            $cy = $location->getCy();
        }

        if ($cx && $cy && $layerId) {

            $timeThreshold = $time - (12 * 3600);
            $sensorRange = $wrapper->getLssSystemData()?->getSensorRange() ?? 0;

            $signatures = $this->flightSignatureRepository->getSignaturesInSensorRange(
                $game->getUser()->getId(),
                $cx,
                $cy,
                $layerId,
                $sensorRange,
                $timeThreshold
            );
            $game->setTemplateVar('SIGNATURES', $signatures);
        }
    }
}
