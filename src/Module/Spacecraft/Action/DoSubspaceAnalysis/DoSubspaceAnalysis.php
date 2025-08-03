<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DoSubspaceAnalysis;

use Override;
use request;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class DoSubspaceAnalysis implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_SUBSPACE';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader
    ) {}


    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $time = request::indInt('time');
        $analyzedshipId = request::indInt('ship_id');
        $flightSigId = request::indInt('flight_sig_id');

        $spacecraft = $wrapper->get();

        $isSubspaceScannerActive = $spacecraft->getSystemState(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);
        if (!$isSubspaceScannerActive) {
            $game->addInformation(_("Das Subraum-Sensorsystem ist nicht aktiv"));
            return;
        }

        $isMatrixScannerHealthy = $spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER);
        if (!$isMatrixScannerHealthy) {
            $game->addInformation(_("Die Matrixsensoren sind nicht betriebsbereit"));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            $game->addInformation(_("Kein EPS-System vorhanden"));
            return;
        }
        if ($epsSystem->getEps() < 100) {
            $game->addInformation(sprintf(_('Es wird 100 Energie für die Analyse benötigt')));
            return;
        }
        $epsSystem->lowerEps(100)->update();
        $subspaceSystem = $wrapper->getSubspaceSystemData();

        if ($subspaceSystem === null) {
            $game->addInformation(_("Kein Subraumfeldsystem vorhanden"));
            return;
        }

        $subspaceSystem->setSpacecraftId($analyzedshipId)->update();
        $subspaceSystem->setAnalyzeTime(time() - (180 - $time))->update();
        $subspaceSystem->setFlightSigId($flightSigId)->update();

        $game->addInformationf('Analyse gestartet. Fertigstellung in ~ %d Sekunden', $time);

        $game->addExecuteJS(
            sprintf('showSystemSettingsWindow(null, "%s"); setAjaxMandatory(false); initializeWarpTraceAnalyzer();', SpacecraftSystemTypeEnum::SUBSPACE_SCANNER->name),
            JavascriptExecutionTypeEnum::AFTER_RENDER
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
