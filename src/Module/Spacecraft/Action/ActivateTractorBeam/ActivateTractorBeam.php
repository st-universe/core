<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\ActivateTractorBeam;

use request;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TractorBeamShipSystem;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipInteractionEnum;
use Stu\Module\Spacecraft\Lib\Interaction\ThreatReactionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ActivateTractorBeam implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACTIVATE_TRACTOR';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private InteractionCheckerInterface $interactionChecker,
        private ActivatorDeactivatorHelperInterface $helper,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private ThreatReactionInterface $threatReaction,
        private TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        private PirateReactionInterface $pirateReaction
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $shipName = $ship->getName();

        if (!$this->interactionChecker->checkPosition($ship, $target)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', self::ACTION_IDENTIFIER);
        }
        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->getInfo()->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
            return;
        }

        if (!TractorBeamShipSystem::isTractorBeamPossible($target)) {
            return;
        }

        $targetName = $target->getName();

        // activate system
        if (!$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, $game->getInfo())) {
            $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
            return;
        }

        if (!$target instanceof Ship) {
            $game->getInfo()->addInformation("Das Ziel kann nicht erfasst werden");
            $this->abort($wrapper, $game);
            return;
        }

        $tractoringShip = $target->getTractoringSpacecraft();
        if ($tractoringShip !== null) {
            $game->getInfo()->addInformation("Das Schiff wird bereits vom Traktorstrahl der " . $tractoringShip->getName() . " gehalten");
            $this->abort($wrapper, $game);
            return;
        }
        if ($target->getHoldingWeb() !== null && $target->getHoldingWeb()->isFinished()) {
            $game->getInfo()->addInformation("Ziel kann nicht erfasst werden, da es in einem Energienetz gefangen ist");
            $this->abort($wrapper, $game);
            return;
        }
        if (
            $target->getFleetId() !== null
            && $ship instanceof Ship
            && $target->getFleetId() == $ship->getFleetId()
        ) {
            $game->getInfo()->addInformation("Die " . $targetName . " befindet sich in der selben Flotte wie die " . $shipName);
            $this->abort($wrapper, $game);
            return;
        }

        if (!$this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_TRACTOR,
            $ship
        )) {

            $this->threatReaction->reactToThreat(
                $wrapper,
                $targetWrapper,
                ShipInteractionEnum::ACTIVATE_TRACTOR
            );
        }

        if ($ship->getCondition()->isDestroyed()) {
            return;
        }
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        //is tractor beam system still healthy?
        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::TRACTOR_BEAM)) {
            $game->getInfo()->addInformation("Der Traktorstrahl wurde bei dem Angriff zerstört");
            return;
        }
        if ($target->getCondition()->isDestroyed()) {
            $game->getInfo()->addInformation("Das Ziel wurde bei dem Angriff zerstört");
            $this->abort($wrapper, $game);
            return;
        }

        //is nbs system still healthy?
        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::NBS)) {
            $game->getInfo()->addInformation("Abbruch, die Nahbereichssensoren wurden bei dem Angriff zerstört");
            $this->abort($wrapper, $game);
            return;
        }


        if ($target->isShielded()) {
            $game->getInfo()->addInformation("Die " . $targetName . " kann aufgrund der aktiven Schilde nicht erfasst werden");
            $this->abort($wrapper, $game);
            return;
        }
        if ($target->isTractoring()) {
            $this->spacecraftSystemManager->deactivate($targetWrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true); //forced active deactivation
        }

        $this->spacecraftStateChanger->changeState($targetWrapper, SpacecraftStateEnum::NONE);

        $target->setDockedTo(null);
        $ship->setTractoredShip($target);
        $this->spacecraftRepository->save($ship);
        $this->spacecraftRepository->save($target);

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            "Die " . $targetName . " wurde in Sektor " . $ship->getSectorString() . " vom Traktorstrahl der " . $shipName . " erfasst",
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $target
        );
        $game->getInfo()->addInformationf("Der Traktorstrahl wurde auf die %s gerichtet", $targetName);

        if ($this->tractorMassPayloadUtil->isTractorSystemStressed($wrapper, $target)) {
            $game->getInfo()->addInformation("[color=yellow]Die Traktoremitter sind überaus beansprucht und könnten beschädigt werden[/color]");
        }
    }

    private function abort(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        //deactivate system
        if (!$this->helper->deactivate($wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, $game->getInfo())) {
            throw new SystemNotDeactivatableException('TRACTOR ERROR');
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
