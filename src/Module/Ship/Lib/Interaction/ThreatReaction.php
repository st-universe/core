<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ThreatReaction implements ThreatReactionInterface
{
    private PlayerRelationDeterminatorInterface $playerRelationDeterminator;

    private ShipAttackCycleInterface $shipAttackCycle;

    private FightLibInterface $fightLib;

    private PrivateMessageSenderInterface $privateMessageSender;

    private GameControllerInterface $game;

    public function __construct(
        PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        ShipAttackCycleInterface $shipAttackCycle,
        FightLibInterface $fightLib,
        PrivateMessageSenderInterface $privateMessageSender,
        GameControllerInterface $game
    ) {
        $this->playerRelationDeterminator = $playerRelationDeterminator;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->fightLib = $fightLib;
        $this->privateMessageSender = $privateMessageSender;
        $this->game = $game;
    }

    public function reactToThreat(
        ShipWrapperInterface $wrapper,
        ShipWrapperInterface $targetWrapper,
        string $cause
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


        $messageCollection = $this->shipAttackCycle->cycle(
            $this->fightLib->getAttackers($targetWrapper),
            [$wrapper],
            true
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
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        return true;
    }
}
