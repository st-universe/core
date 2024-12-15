<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StartTakeover;

use Override;
use request;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Maindesk\Action\ColonizationShip\ColonizationShip;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class StartTakeover implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TAKEOVER_SHIP';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private InteractionCheckerInterface $interactionChecker,
        private NbsUtilityInterface $nbsUtility,
        private FightLibInterface $fightLib,
        private ShipTakeoverManagerInterface $shipTakeoverManager,
        private SpacecraftStateChangerInterface $spacecraftStateChanger
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        if ($ship->getTakeoverActive() !== null) {
            return;
        }

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();


        if (!$target->isBoardingPossible()) {
            return;
        }

        if ($target->getUser() === $user) {
            return;
        }

        if ($target->getUser()->isNpc()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler ist NPC!'));
            return;
        }

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($target, $ship)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', self::ACTION_IDENTIFIER);
        }

        if (!$this->fightLib->canAttackTarget($ship, $target, false, false)) {
            throw new SanityCheckException('Target cant be attacked', self::ACTION_IDENTIFIER);
        }

        if ($target->getCloakState() && !$this->nbsUtility->isTachyonActive($ship)) {
            throw new SanityCheckException('Attacked cloaked ship without active tachyon', self::ACTION_IDENTIFIER);
        }

        $epsSystemData = $wrapper->getEpsSystemData();
        if ($epsSystemData === null || $epsSystemData->getEps() === 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }

        if ($ship->isDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }

        if ($this->fightLib->isTargetOutsideFinishedTholianWeb($ship, $target)) {
            $game->addInformation(_('Das Ziel ist nicht mit im Energienetz gefangen'));
            return;
        }

        if (!$target->getCrewAssignments()->isEmpty()) {
            $game->addInformation(_('Aktion nicht möglich, das Ziel ist bemannt'));
            return;
        }

        if ($target->getRumpId() && in_array($target->getRumpId(), [
            ColonizationShip::FED_COL_BUILDPLAN,
            ColonizationShip::ROM_COL_BUILDPLAN,
            ColonizationShip::KLING_COL_BUILDPLAN,
            ColonizationShip::CARD_COL_BUILDPLAN,
            ColonizationShip::FERG_COL_BUILDPLAN
        ])) {
            $game->addInformation(_('Dieses Schiff ist nicht zur Übernahme geeignet'));
            return;
        }


        if ($target->getTakeoverPassive() !== null) {
            $game->addInformationf(
                'Aktion nicht möglich, das Ziel ist bereits im Begriff von der %s übernommen zu werden',
                $target->getTakeoverPassive()->getSourceSpacecraft()->getName()
            );
            return;
        }

        $neededPrestige = $this->shipTakeoverManager->getPrestigeForTakeover($target);
        if ($user->getPrestige() < $neededPrestige) {
            $game->addInformation(sprintf(
                'Nicht genügend Prestige vorhanden, benötigt wird: %d',
                $neededPrestige
            ));
            return;
        }

        $this->spacecraftStateChanger->changeShipState($wrapper, SpacecraftStateEnum::SHIP_STATE_ACTIVE_TAKEOVER);
        $this->shipTakeoverManager->startTakeover($ship, $target, $neededPrestige);

        $game->addInformationf(
            'Übernahme der %s wurde gestartet. Fertigstellung in %d Runden.',
            $target->getName(),
            ShipTakeoverManagerInterface::TURNS_TO_TAKEOVER
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
