<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Override;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;

final class ShipAttackCycle implements ShipAttackCycleInterface
{

    public function __construct(
        private EnergyWeaponPhaseInterface $energyWeaponPhase,
        private ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        private AttackerProviderFactoryInterface $attackerProviderFactory,
        private AttackMatchupInterface $attackMatchup,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private ShipAttackPreparationInterface $shipAttackPreparation,
        private MessageFactoryInterface $messageFactory
    ) {
    }

    #[Override]
    public function cycle(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        ShipAttackCauseEnum $attackCause
    ): MessageCollectionInterface {

        $messages = $this->messageFactory->createMessageCollection();
        $isOneWay = $attackCause->isOneWay();

        $this->shipAttackPreparation->getReady($attackers, $defenders, $isOneWay, $messages);

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

            $shipAttacker = $this->attackerProviderFactory->getShipAttacker($matchup->getAttacker());

            $this->energyWeaponPhase->fire(
                $shipAttacker,
                $targetBattleParty,
                $attackCause,
                $messages
            );

            $this->projectileWeaponPhase->fire(
                $shipAttacker,
                $targetBattleParty,
                $attackCause,
                $messages
            );
        }

        $attackersRoundBasedBattleParty->saveActiveMembers();
        $defendersRoundBasedBattleParty->saveActiveMembers();

        return $messages;
    }
}
