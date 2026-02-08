<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ThreatReaction implements ThreatReactionInterface
{
    public function __construct(
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private SpacecraftAttackCycleInterface $spacecraftAttackCycle,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private PrivateMessageSenderInterface $privateMessageSender,
        private GameControllerInterface $game
    ) {}

    #[\Override]
    public function reactToThreat(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
        ShipInteractionEnum $interaction
    ): bool {

        $target = $targetWrapper->get();
        if ($targetWrapper->isUnalerted()) {
            return false;
        }

        $ship = $wrapper->get();
        $user = $ship->getUser();
        if ($target->getUser()->getId() === $user->getId()) {
            return false;
        }

        if ($this->playerRelationDeterminator->isFriend($target->getUser(), $user)) {
            return false;
        }

        $cause = $interaction->getInteractionText($wrapper, $targetWrapper);

        $attackingBattleParty = $this->battlePartyFactory->createAttackingBattleParty($targetWrapper, false);

        $messageCollection = $this->spacecraftAttackCycle->cycle(
            $attackingBattleParty,
            $this->battlePartyFactory->createSingletonBattleParty($wrapper),
            $interaction->getAttackCause()
        );

        if ($messageCollection->isEmpty()) {
            return false;
        }

        $informations = $messageCollection->getInformationDump();
        $this->game->getInfo()->addInformationWrapper($informations);

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
