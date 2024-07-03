<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Override;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ThreatReaction implements ThreatReactionInterface
{
    public function __construct(
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private ShipAttackCycleInterface $shipAttackCycle,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private PrivateMessageSenderInterface $privateMessageSender,
        private GameControllerInterface $game
    ) {
    }

    #[Override]
    public function reactToThreat(
        ShipWrapperInterface $wrapper,
        ShipWrapperInterface $targetWrapper,
        ShipInteractionEnum $interaction
    ): bool {

        $target = $targetWrapper->get();
        if ($target->getAlertState() === ShipAlertStateEnum::ALERT_GREEN) {
            return false;
        }

        $ship = $wrapper->get();
        $user = $ship->getUser();
        if ($target->getUser() === $user) {
            return false;
        }

        if ($this->playerRelationDeterminator->isFriend($target->getUser(), $user)) {
            return false;
        }

        $cause = $interaction->getInteractionText($wrapper, $targetWrapper);

        $attackingBattleParty = $this->battlePartyFactory->createAttackingBattleParty($targetWrapper);

        $messageCollection = $this->shipAttackCycle->cycle(
            $attackingBattleParty,
            $this->battlePartyFactory->createSingletonBattleParty($wrapper),
            $interaction->getAttackCause()
        );

        if ($messageCollection->isEmpty()) {
            return false;
        }

        $informations = $messageCollection->getInformationDump();
        $this->game->addInformationWrapper($informations);

        $this->privateMessageSender->send(
            $user->getId(),
            $target->getUser()->getId(),
            sprintf(
                "%s\nFolgende Aktionen wurden ausgeführt:\n%s",
                $cause,
                $informations->getInformationsAsString()
            ),
            $attackingBattleParty->getPrivateMessageType()
        );

        return true;
    }
}
