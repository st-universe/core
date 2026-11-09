<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use RuntimeException;
use Stu\Component\Borg\BorgSphereAttackContext;
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
        private MessageFactoryInterface $messageFactory,
        private ?BorgSphereAttackContext $borgSphereAttackContext = null
    ) {}

    #[\Override]
    public function cycle(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        SpacecraftAttackCauseEnum $attackCause
    ): MessageCollectionInterface {
        $this->borgSphereAttackContext?->beginBattle();
        try {
            return $this->executeCycle($attackers, $defenders, $attackCause);
        } finally {
            $this->borgSphereAttackContext?->endBattle();
        }
    }

    private function executeCycle(
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

        $maxMatchups = $this->getMaxMatchups($attackers, $defenders, $isOneWay);
        $matchupCount = 0;

        while ($this->borgSphereAttackContext?->isBattleStopped() !== true) {
            $matchup = $this->attackMatchup->getMatchup(
                $attackersRoundBasedBattleParty,
                $defendersRoundBasedBattleParty,
                $firstStrike,
                $isOneWay
            );
            if ($matchup === null) {
                break;
            }
            $matchupCount++;
            $this->checkMatchupLimit($matchupCount, $maxMatchups, $attackCause, $attackers, $defenders);

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

            if ($this->borgSphereAttackContext?->isBattleStopped()) {
                break;
            }

            $this->projectileWeaponPhase->fire(
                $spacecraftAttacker,
                $targetBattleParty,
                $attackCause,
                $messages
            );
        }

        return $messages;
    }

    private function checkMatchupLimit(int $count, int $maximum, SpacecraftAttackCauseEnum $cause, BattlePartyInterface $attackers, BattlePartyInterface $defenders): void
    {
        if ($count > $maximum) {
            throw new RuntimeException(sprintf(
                'Attack cycle exceeded matchup limit of %d for cause %s, attackersUser=%d attackersLeader=%d defendersUser=%d defendersLeader=%d',
                $maximum,
                $cause->name,
                $attackers->getUser()->getId(),
                $attackers->getLeader()->get()->getId(),
                $defenders->getUser()->getId(),
                $defenders->getLeader()->get()->getId()
            ));
        }
    }

    private function getMaxMatchups(
        BattlePartyInterface $attackers,
        BattlePartyInterface $defenders,
        bool $isOneWay
    ): int {
        return $attackers->getActiveMembers(true)->count()
            + ($isOneWay ? 0 : $defenders->getActiveMembers(true)->count());
    }
}
