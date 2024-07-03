<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;

final class ShipAttackPreparation implements ShipAttackPreparationInterface
{

    public function __construct(
        private FightLibInterface $fightLib,
        private MessageFactoryInterface $messageFactory
    ) {
    }

    public function getReady(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        bool $isOneWay,
        MessageCollectionInterface $messages
    ): void {
        foreach ($attackers->getActiveMembers() as $attacker) {
            $message = $this->messageFactory->createMessage(
                $attacker->get()->getUser()->getId()
            );
            $this->fightLib->ready($attacker, $message);
            $messages->add($message);
        }
        if (!$isOneWay) {
            foreach ($defenders->getActiveMembers() as $defender) {
                $message = $this->messageFactory->createMessage(
                    $defender->get()->getUser()->getId()
                );
                $this->fightLib->ready($defender, $message);
                $messages->add($message);
            }
        }
    }
}
