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
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class AttackTrackedShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ATTACK_TRACKED';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private PrivateMessageSenderInterface $privateMessageSender,
        private ShipAttackCycleInterface $shipAttackCycle,
        private InteractionCheckerInterface $interactionChecker,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private FightLibInterface $fightLib,
        private BattlePartyFactoryInterface $battlePartyFactory
    ) {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
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

        $tracker = $wrapper->getTrackerSystemData();
        if ($tracker === null || $tracker->targetId !== $target->getId()) {
            return;
        }

        if (!$ship->hasActiveWeapon()) {
            $game->addInformation(_('Waffen sind offline'));
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

        $epsSystemData = $wrapper->getEpsSystemData();
        if ($epsSystemData === null || $epsSystemData->getEps() === 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($ship->isDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }
        if ($ship->getDockedTo() !== null) {
            $ship->setDockedTo(null);
        }

        $isTargetBase = $target->isBase();
        $isShipWarped = $ship->getWarpDriveState();
        $isTargetWarped = $target->getWarpDriveState();

        [$attacker, $defender, $fleet] = $this->fightLib->getAttackersAndDefenders(
            $wrapper,
            $targetWrapper,
            $this->battlePartyFactory
        );

        $messageCollection = $this->shipAttackCycle->cycle($attacker, $defender, ShipAttackCauseEnum::SHIP_FIGHT);

        $informations = $messageCollection->getInformationDump();

        $pm = sprintf(
            _("Kampf in Sektor %s\n%s"),
            $ship->getSectorString(),
            $informations->getInformationsAsString()
        );
        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            $pm,
            $isTargetBase ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );

        //Alarm-Rot check for ship
        if ($isShipWarped && !$ship->isDestroyed()) {
            $this->alertReactionFacade->doItAll($wrapper, $informations);
        }

        //Alarm-Rot check for traktor ship
        if ($isTargetWarped && !$target->isDestroyed()) {
            $this->alertReactionFacade->doItAll($targetWrapper, $informations);
        }

        if ($ship->isDestroyed()) {
            $game->addInformationWrapper($informations);
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

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
