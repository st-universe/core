<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Battle\Message\FightMessage;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollection;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipAttackCycle implements ShipAttackCycleInterface
{
    private ShipRepositoryInterface $shipRepository;

    private EnergyWeaponPhaseInterface $energyWeaponPhase;

    private ProjectileWeaponPhaseInterface $projectileWeaponPhase;

    private FightLibInterface $fightLib;

    private AttackerProviderFactoryInterface $attackerProviderFactory;

    private AttackMatchupInterface $attackMatchup;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        EnergyWeaponPhaseInterface $energyWeaponPhase,
        ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        FightLibInterface $fightLib,
        AttackerProviderFactoryInterface $attackerProviderFactory,
        AttackMatchupInterface $attackMatchup
    ) {
        $this->shipRepository = $shipRepository;
        $this->energyWeaponPhase = $energyWeaponPhase;
        $this->projectileWeaponPhase = $projectileWeaponPhase;
        $this->fightLib = $fightLib;
        $this->attackerProviderFactory = $attackerProviderFactory;
        $this->attackMatchup = $attackMatchup;
    }

    public function cycle(
        array $attackers,
        array $defenders,
        bool $oneWay = false,
        bool $isAlertRed = false
    ): FightMessageCollectionInterface {
        $fightMessages = new FightMessageCollection();

        $this->getReady($attackers, $defenders, $oneWay, $fightMessages);

        $firstStrike = true;
        $usedShipIds = [];

        while (true) {
            $matchup = $this->attackMatchup->getMatchup(
                $attackers,
                $defenders,
                $usedShipIds,
                $firstStrike,
                $oneWay
            );
            if ($matchup === null) {
                break;
            }

            $targetShipWrappers = $matchup->getDefenders();
            $firstStrike = false;

            $shipAttacker = $this->attackerProviderFactory->getShipAttacker($matchup->getAttacker());

            $fightMessages->addMultiple($this->energyWeaponPhase->fire(
                $shipAttacker,
                $targetShipWrappers,
                $isAlertRed
            ));

            $fightMessages->addMultiple($this->projectileWeaponPhase->fire(
                $shipAttacker,
                $this->fightLib->filterInactiveShips($targetShipWrappers),
                $isAlertRed
            ));
        }

        foreach ($attackers as $wrapper) {
            $this->shipRepository->save($wrapper->get());
        }

        foreach ($defenders as $wrapper) {
            $this->shipRepository->save($wrapper->get());
        }

        return $fightMessages;
    }

    /**
     * @param array<ShipWrapperInterface> $attackers
     * @param array<ShipWrapperInterface> $defenders
     */
    private function getReady(
        array $attackers,
        array $defenders,
        bool $oneWay,
        FightMessageCollectionInterface $fightMessages
    ): void {
        foreach ($attackers as $attacker) {
            $fightMessage = new FightMessage(
                $attacker->get()->getUser()->getId(),
                null,
                $this->fightLib->ready($attacker)->getInformations()
            );
            $fightMessages->add($fightMessage);
        }
        if (!$oneWay) {
            foreach ($defenders as $defender) {
                $fightMessage = new FightMessage(
                    $defender->get()->getUser()->getId(),
                    null,
                    $this->fightLib->ready($defender)->getInformations()
                );
                $fightMessages->add($fightMessage);
            }
        }
    }
}
