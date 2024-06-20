<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;

final class ShipAttackPreparation implements ShipAttackPreparationInterface
{

    public function __construct(
        private FightLibInterface $fightLib
    ) {
    }

    public function getReady(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        bool $isOneWay,
        MessageCollectionInterface $messages
    ): void {
        foreach ($attackers->getActiveMembers() as $attacker) {
            $message = new Message(
                $attacker->get()->getUser()->getId(),
                null,
                $this->fightLib->ready($attacker)->getInformations()
            );
            $messages->add($message);
        }
        if (!$isOneWay) {
            foreach ($defenders->getActiveMembers() as $defender) {
                $message = new Message(
                    $defender->get()->getUser()->getId(),
                    null,
                    $this->fightLib->ready($defender)->getInformations()
                );
                $messages->add($message);
            }
        }
    }
}
