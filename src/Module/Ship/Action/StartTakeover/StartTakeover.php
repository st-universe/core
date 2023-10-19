<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartTakeover;

use request;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class StartTakeover implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TAKEOVER_SHIP';

    private ShipLoaderInterface $shipLoader;

    private InteractionCheckerInterface $interactionChecker;

    private NbsUtilityInterface $nbsUtility;

    private FightLibInterface $fightLib;

    private ShipTakeoverManagerInterface $shipTakeoverManager;

    private ShipStateChangerInterface $shipStateChanger;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        InteractionCheckerInterface $interactionChecker,
        NbsUtilityInterface $nbsUtility,
        FightLibInterface $fightLib,
        ShipTakeoverManagerInterface $shipTakeoverManager,
        ShipStateChangerInterface $shipStateChanger
    ) {
        $this->shipLoader = $shipLoader;
        $this->interactionChecker = $interactionChecker;
        $this->nbsUtility = $nbsUtility;
        $this->fightLib = $fightLib;
        $this->shipTakeoverManager = $shipTakeoverManager;
        $this->shipStateChanger = $shipStateChanger;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
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

        if (!$this->fightLib->canAttackTarget($ship, $target, false)) {
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

        $neededPrestige = $this->shipTakeoverManager->getPrestigeForTakeover($target);
        if ($user->getPrestige() < $neededPrestige) {
            $game->addInformation(sprintf(
                'Nicht genügend Prestige vorhanden, benötigt wird: %d',
                $neededPrestige
            ));
            return;
        }

        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_ACTIVE_TAKEOVER);
        $this->shipTakeoverManager->startTakeover($ship, $target, $neededPrestige);

        $game->addInformationf(
            'Übernahme der %s wurde gestartet. Fertigstellung in %d Runden.',
            $target->getName(),
            ShipTakeoverManagerInterface::TURNS_TO_TAKEOVER
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
