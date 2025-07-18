<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemWrapperFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

class SubspaceSensorSystemSettings implements SystemSettingsProviderInterface
{
    public function __construct(
        private readonly SpacecraftSystemWrapperFactoryInterface $spacecraftSystemWrapperFactory,
        private FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {}

    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {
        $game->setMacroInAjaxWindow('html/spacecraft/system/subspaceScanner.twig');

        $user = $game->getUser();
        $spacecraft = $wrapper->get();

        $isSubspaceScannerActive = $spacecraft->getSystemState(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);


        $isMatrixScannerHealthy = $spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER);
        if ($isMatrixScannerHealthy && $isSubspaceScannerActive) {

            $location = $spacecraft->getLocation();
            $layerId = null;

            $subspaceSystemData = $wrapper->getSubspaceSystemData();
            if ($subspaceSystemData === null) {
                return;
            }

            $flightSigId = $subspaceSystemData->getFlightSigId();
            if ($flightSigId) {
                $flightSig = $this->flightSignatureRepository->find($flightSigId);
                $game->setTemplateVar('ANALYZED_SIGNATURE', $flightSig);
            }

            $analyzeTime = $subspaceSystemData->getAnalyzeTime();
            if ($analyzeTime) {
                $currentTime = time();
                $maxTime = $analyzeTime + (10 * 60);
                $game->setTemplateVar('ANALYZE_TIME', $analyzeTime);

                if ($currentTime <= $maxTime) {
                    $game->setTemplateVar('ANALYZE_TIME', $analyzeTime);
                }
            }


            $system = $spacecraft->getSystem();
            if ($system) {
                $cx = $system->getCx();
                $cy = $system->getCy();
            } else {
                $cx = $location->getCx();
                $cy = $location->getCy();
            }

            if ($location->getLayer()) {
                $layerId = $location->getLayer()->getId();
            }
            $timeThreshold = time() - (12 * 3600);

            $sensorRange = $wrapper->getLssSystemData()?->getSensorRange() ?? 0;

            if ($cx && $cy && $layerId) {
                $signatures = $this->flightSignatureRepository->getSignaturesInSensorRange(
                    $user->getId(),
                    $cx,
                    $cy,
                    $layerId,
                    $sensorRange,
                    $timeThreshold
                );
                $game->setTemplateVar('SIGNATURES', $signatures);
            }
        } else {
            $game->setTemplateVar('SYSTEMWARNING', true);
        }



        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar('SPACECRAFT', $spacecraft);
        $game->setTemplateVar(
            'systemWrapper',
            $this->spacecraftSystemWrapperFactory->create($wrapper->get(), $systemType)
        );
    }
}
