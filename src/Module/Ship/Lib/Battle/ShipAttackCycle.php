<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\MessageCollection;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;

final class ShipAttackCycle implements ShipAttackCycleInterface
{

    public function __construct(
        private EnergyWeaponPhaseInterface $energyWeaponPhase,
        private ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        private AttackerProviderFactoryInterface $attackerProviderFactory,
        private AttackMatchupInterface $attackMatchup,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private ShipAttackPreparationInterface $shipAttackPreparation
    ) {
    }

    public function cycle(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        ShipAttackCauseEnum $attackCause
    ): MessageCollectionInterface {

        $messages = new MessageCollection();
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

            $messages->addMultiple($this->energyWeaponPhase->fire(
                $shipAttacker,
                $targetBattleParty,
                $attackCause
            ));

            $messages->addMultiple($this->projectileWeaponPhase->fire(
                $shipAttacker,
                $targetBattleParty,
                $attackCause
            ));
        }

        $attackersRoundBasedBattleParty->saveActiveMembers();
        $defendersRoundBasedBattleParty->saveActiveMembers();

        return $messages;
    }
}
