<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipNfsItem;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\User;

final class FightLib implements FightLibInterface
{
    public function __construct(
        private ShipSystemManagerInterface $shipSystemManager,
        private  CancelRepairInterface $cancelRepair,
        private AlertLevelBasedReactionInterface $alertLevelBasedReaction
    ) {
    }

    public function ready(ShipWrapperInterface $wrapper): InformationWrapper
    {
        $ship = $wrapper->get();

        $informations = new InformationWrapper();

        if (
            $ship->isDestroyed()
            || $ship->getRump()->isEscapePods()
        ) {
            return $informations;
        }
        if ($ship->getBuildplan() === null) {
            return $informations;
        }
        if (!$ship->hasEnoughCrew()) {
            return $informations;
        }

        if ($ship->getDockedTo() !== null) {
            $ship->setDockedTo(null);
            $informations->addInformation("- Das Schiff hat abgedockt");
        }

        try {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
        } catch (ShipSystemException $e) {
        }
        try {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK);
        } catch (ShipSystemException $e) {
        }

        $this->cancelRepair->cancelRepair($ship);

        $informations->addInformationWrapper($this->alertLevelBasedReaction->react($wrapper));

        if (!$informations->isEmpty()) {
            $informations->addInformationArray([sprintf(_('Aktionen der %s'), $ship->getName())], true);
        }

        return $informations;
    }

    public function canAttackTarget(
        ShipInterface $ship,
        ShipInterface|ShipNfsItem $target,
        bool $checkCloaked = false,
        bool $checkActiveWeapons = true,
        bool $checkWarped = true
    ): bool {
        if ($checkActiveWeapons && !$ship->hasActiveWeapon()) {
            return false;
        }

        //can't attack itself
        if ($target === $ship) {
            return false;
        }

        //can't attack trumfields
        if ($target->isTrumfield()) {
            return false;
        }

        //can't attack cloaked target
        if ($checkCloaked && $target->getCloakState()) {
            return false;
        }

        //if tractored, can only attack tractoring ship
        $tractoringShip = $ship->getTractoringShip();
        if ($tractoringShip !== null) {
            return $target->getId() === $tractoringShip->getId();
        }

        //can't attack target under warp
        if ($checkWarped && $target->isWarped()) {
            return false;
        }

        //can't attack own target under cloak
        if (
            $target->getUserId() === $ship->getUserId()
            && $target->getCloakState()
        ) {
            return false;
        }

        //can't attack same fleet
        $ownFleetId = $ship->getFleetId();
        $targetFleetId = $target->getFleetId();
        if ($ownFleetId === null || $targetFleetId === null) {
            return true;
        }

        return $ownFleetId !== $targetFleetId;
    }

    public function getAttackersAndDefenders(
        ShipWrapperInterface|FleetWrapperInterface $wrapper,
        ShipWrapperInterface $targetWrapper,
        BattlePartyFactoryInterface $battlePartyFactory
    ): array {
        $attackers = $battlePartyFactory->createAttackingBattleParty($wrapper);
        $defenders = $battlePartyFactory->createAttackedBattleParty($targetWrapper);

        return [
            $attackers,
            $defenders,
            count($attackers) + count($defenders) > 2
        ];
    }

    public function isTargetOutsideFinishedTholianWeb(ShipInterface $ship, ShipInterface $target): bool
    {
        $web = $ship->getHoldingWeb();
        if ($web === null) {
            return false;
        }

        return $web->isFinished() && ($target->getHoldingWeb() !== $web);
    }

    public static function isBoardingPossible(ShipInterface|ShipNfsItem $ship): bool
    {
        return !(User::isUserNpc($ship->getUserId())
            || $ship->isBase()
            || $ship->isTrumfield()
            || $ship->getCloakState()
            || $ship->getShieldState()
            || $ship->isWarped());
    }

    public function calculateHealthPercentage(ShipInterface $target): int
    {
        $shipCount = 0;
        $healthSum = 0;

        $fleet = $target->getFleet();
        if ($fleet !== null) {
            foreach ($fleet->getShips() as $ship) {
                $shipCount++;
                $healthSum += $ship->getHealthPercentage();
            }
        } else {
            $shipCount++;
            $healthSum += $target->getHealthPercentage();
        }

        return (int)($healthSum / $shipCount);
    }
}
