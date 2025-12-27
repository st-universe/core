<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;

final class SpacecraftAttackCycle implements SpacecraftAttackCycleInterface
{
    public function __construct(
        private EnergyWeaponPhaseInterface $energyWeaponPhase,
        private ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        private AttackerProviderFactoryInterface $attackerProviderFactory,
        private AttackMatchupInterface $attackMatchup,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private SpacecraftAttackPreparationInterface $spacecraftAttackPreparation,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[\Override]
    public function cycle(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        SpacecraftAttackCauseEnum $attackCause
    ): MessageCollectionInterface {

        $messages = $this->messageFactory->createMessageCollection();
        $isOneWay = $attackCause->isOneWay();

        $this->spacecraftAttackPreparation->getReady($attackers, $defenders, $isOneWay, $messages);

        $firstStrike = true;

        $attackersRoundBasedBattleParty = $this->battlePartyFactory->createRoundBasedBattleParty($attackers);
        $defendersRoundBasedBattleParty = $this->battlePartyFactory->createRoundBasedBattleParty($defenders);

        while (true) {
            $matchup = $this->attackMatchup->getMatchup(
                $attackersRoundBasedBattleParty,
                $defendersRoundBasedBattleParty,
                $firstStrike,
                $isOneWay
            );
            if ($matchup === null) {
                break;
            }

            $targetBattleParty = $matchup->getDefenders();
            $firstStrike = false;

            $spacecraftAttacker = $this->attackerProviderFactory->createSpacecraftAttacker(
                $matchup->getAttacker(),
                $matchup->isAttackingShieldsOnly()
            );

            $this->energyWeaponPhase->fire(
                $spacecraftAttacker,
                $targetBattleParty,
                $attackCause,
                $messages
            );

            $this->projectileWeaponPhase->fire(
                $spacecraftAttacker,
                $targetBattleParty,
                $attackCause,
                $messages
            );
        }

        return $messages;
    }
}
