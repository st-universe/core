<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackTrackedShip;

use Override;
use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class AttackTrackedShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ATTACK_TRACKED';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftAttackCycleInterface $spacecraftAttackCycle,
        private InteractionCheckerInterface $interactionChecker,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private FightLibInterface $fightLib,
        private BattlePartyFactoryInterface $battlePartyFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $spacecraftId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $spacecraftId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $spacecraft = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $tracker = $wrapper->getTrackerSystemData();
        if ($tracker === null || $tracker->targetId !== $target->getId()) {
            return;
        }

        if (!$spacecraft->hasActiveWeapon()) {
            $game->addInformation(_('Waffen sind offline'));
            return;
        }

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if (!$spacecraft->hasEnoughCrew($game)) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($target, $spacecraft)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', self::ACTION_IDENTIFIER);
        }

        $epsSystemData = $wrapper->getEpsSystemData();
        if ($epsSystemData === null || $epsSystemData->getEps() === 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($spacecraft->getCondition()->isDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }

        $spacecraft->setDockedTo(null);

        $isTargetBase = $target->isStation();
        $isShipWarped = $spacecraft->getWarpDriveState();
        $isTargetWarped = $target->getWarpDriveState();

        [$attacker, $defender, $fleet] = $this->fightLib->getAttackersAndDefenders(
            $wrapper,
            $targetWrapper,
            false,
            $this->battlePartyFactory
        );

        $messageCollection = $this->spacecraftAttackCycle->cycle($attacker, $defender, SpacecraftAttackCauseEnum::SHIP_FIGHT);

        $informations = $messageCollection->getInformationDump();

        $pm = sprintf(
            _("Kampf in Sektor %s\n%s"),
            $spacecraft->getSectorString(),
            $informations->getInformationsAsString()
        );
        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            $pm,
            $isTargetBase ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );

        //Alarm-Rot check for ship
        if ($isShipWarped && !$spacecraft->getCondition()->isDestroyed()) {
            $this->alertReactionFacade->doItAll($wrapper, $informations);
        }

        //Alarm-Rot check for traktor ship
        if ($isTargetWarped && !$target->getCondition()->isDestroyed()) {
            $this->alertReactionFacade->doItAll($targetWrapper, $informations);
        }

        if ($spacecraft->getCondition()->isDestroyed()) {
            $game->addInformationWrapper($informations);
            return;
        }
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if ($fleet) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $informations->getInformations());
        } else {
            $game->addInformationWrapper($informations);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
