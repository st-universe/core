<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Override;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;

final class SpacecraftAttackPreparation implements SpacecraftAttackPreparationInterface
{
    public function __construct(
        private FightLibInterface $fightLib,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
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
            $this->fightLib->ready($attacker, true, $message);
            $messages->add($message);
        }
        if (!$isOneWay) {
            $isUndockingMandatory = $defenders->isActive();
            foreach ($defenders->getActiveMembers() as $defender) {
                $message = $this->messageFactory->createMessage(
                    $defender->get()->getUser()->getId()
                );
                $this->fightLib->ready($defender, $isUndockingMandatory, $message);
                $messages->add($message);
            }
        }
    }
}
