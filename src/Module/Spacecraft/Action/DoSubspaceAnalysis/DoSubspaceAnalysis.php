<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DoSubspaceAnalysis;

use Psr\EventDispatcher\EventDispatcherInterface;
use request;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DoSubspaceAnalysis implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_SUBSPACE';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private FlightSignatureRepositoryInterface $flightSignatureRepository,
        private UserRepositoryInterface $userRepository,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private EventDispatcherInterface $eventDispatcher
    ) {}


    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $context->getUser()->getId();

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
            $context->getInfo()->addInformation(_("Das Subraum-Sensorsystem ist nicht aktiv"));
            return;
        }

        $isMatrixScannerHealthy = $spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER);
        if (!$isMatrixScannerHealthy) {
            $context->getInfo()->addInformation(_("Die Matrixsensoren sind nicht betriebsbereit"));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            $context->getInfo()->addInformation(_("Kein EPS-System vorhanden"));
            return;
        }
        if ($epsSystem->getEps() < 100) {
            $context->getInfo()->addInformation(_('Es wird 100 Energie für die Analyse benötigt'));
            return;
        }
        $epsSystem->lowerEps(100)->update();
        $subspaceSystem = $wrapper->getSubspaceSystemData();

        if ($subspaceSystem === null) {
            $context->getInfo()->addInformation(_("Kein Subraumfeldsystem vorhanden"));
            return;
        }

        $subspaceSystem->setSpacecraftId($analyzedshipId)->update();
        $subspaceSystem->setAnalyzeTime(time() - (180 - $time))->update();
        $subspaceSystem->setFlightSigId($flightSigId)->update();

        $flightSignature = $this->flightSignatureRepository->find($flightSigId);
        $targetUser = $flightSignature === null ? null : $this->userRepository->find($flightSignature->getUserId());
        if (
            $targetUser !== null
            && !$targetUser->isNpc()
            && !$this->playerRelationDeterminator->isFriend($spacecraft->getUser(), $targetUser)
        ) {
            $this->eventDispatcher->dispatch(new CrewExperienceEvent(
                $spacecraft,
                SkillEnhancementEnum::START_FOREIGN_SUBSPACE_ANALYSIS
            ));
        }

        $context->getInfo()->addInformationf('Analyse gestartet. Fertigstellung in ~ %d Sekunden', $time);

        $context->addExecuteJS(
            sprintf('showSystemSettingsWindow(null, "%s"); setAjaxMandatory(false); initializeWarpTraceAnalyzer();', SpacecraftSystemTypeEnum::SUBSPACE_SCANNER->name),
            JavascriptExecutionTypeEnum::AFTER_RENDER
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
