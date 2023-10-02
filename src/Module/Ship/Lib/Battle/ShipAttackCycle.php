<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollection;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
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
    ): MessageCollectionInterface {
        $messages = new MessageCollection();

        $this->getReady($attackers, $defenders, $oneWay, $messages);

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

            $messages->addMultiple($this->energyWeaponPhase->fire(
                $shipAttacker,
                $targetShipWrappers,
                $isAlertRed
            ));

            $messages->addMultiple($this->projectileWeaponPhase->fire(
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

        return $messages;
    }

    /**
     * @param array<ShipWrapperInterface> $attackers
     * @param array<ShipWrapperInterface> $defenders
     */
    private function getReady(
        array $attackers,
        array $defenders,
        bool $oneWay,
        MessageCollectionInterface $messages
    ): void {
        foreach ($attackers as $attacker) {
            $message = new Message(
                $attacker->get()->getUser()->getId(),
                null,
                $this->fightLib->ready($attacker)->getInformations()
            );
            $messages->add($message);
        }
        if (!$oneWay) {
            foreach ($defenders as $defender) {
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
