<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipNfsItem;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\User;

final class FightLib implements FightLibInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private CancelRepairInterface $cancelRepair;

    private AlertLevelBasedReactionInterface $alertLevelBasedReaction;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        CancelRepairInterface $cancelRepair,
        AlertLevelBasedReactionInterface $alertLevelBasedReaction
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->cancelRepair = $cancelRepair;
        $this->alertLevelBasedReaction = $alertLevelBasedReaction;
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

    public function filterInactiveShips(array $base): array
    {
        return array_filter(
            $base,
            fn (ShipWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed() && !$wrapper->get()->isDisabled()
        );
    }

    public function canFire(ShipWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();
        if (!$ship->getNbs()) {
            return false;
        }
        if (!$ship->hasActiveWeapon()) {
            return false;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        return $epsSystem !== null && $epsSystem->getEps() !== 0;
    }

    public function canAttackTarget(
        ShipInterface $ship,
        ShipInterface|ShipNfsItem $target,
        bool $checkActiveWeapons = true
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

        //if tractored, can only attack tractoring ship
        $tractoringShip = $ship->getTractoringShip();
        if ($tractoringShip !== null) {
            return $target->getId() === $tractoringShip->getId();
        }

        //can't attack target under warp
        if ($target->getWarpState()) {
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

    public function getAttackersAndDefenders(ShipWrapperInterface $wrapper, ShipWrapperInterface $targetWrapper): array
    {
        $attackers = $this->getAttackers($wrapper);
        $defenders = $this->getDefenders($targetWrapper);

        return [
            $attackers,
            $defenders,
            count($attackers) + count($defenders) > 2
        ];
    }

    /** @return array<int, ShipWrapperInterface> */
    public function getAttackers(ShipWrapperInterface $wrapper): array
    {
        $ship = $wrapper->get();
        $fleet = $wrapper->getFleetWrapper();

        if ($ship->isFleetLeader() && $fleet !== null) {
            $attackers = $fleet->getShipWrappers();
        } else {
            $attackers = [$ship->getId() => $wrapper];
        }

        return $attackers;
    }

    /** @return array<int, ShipWrapperInterface> */
    private function getDefenders(ShipWrapperInterface $targetWrapper): array
    {
        $target = $targetWrapper->get();
        $targetFleet = $targetWrapper->getFleetWrapper();

        if ($targetFleet !== null) {
            $defenders = [];

            // only uncloaked defenders fight
            foreach ($targetFleet->getShipWrappers() as $shipId => $defWrapper) {

                $defShip = $defWrapper->get();
                if (!$defShip->getCloakState()) {
                    $defenders[$shipId] = $defWrapper;

                    $this->addDockedToAsDefender($targetWrapper, $defenders);
                }
            }

            // if all defenders were cloaked, they obviously were scanned and enter the fight as a whole fleet
            if ($defenders === []) {
                $defenders = $targetFleet->getShipWrappers();
            }
        } else {
            $defenders = [$target->getId() => $targetWrapper];

            $this->addDockedToAsDefender($targetWrapper, $defenders);
        }

        return $defenders;
    }

    /** @param array<int, ShipWrapperInterface> $defenders */
    private function addDockedToAsDefender(ShipWrapperInterface $targetWrapper, array &$defenders): void
    {
        $dockedToWrapper = $targetWrapper->getDockedToShipWrapper();
        if (
            $dockedToWrapper !== null
            && !$dockedToWrapper->get()->getUser()->isNpc()
            && $dockedToWrapper->get()->hasActiveWeapon()
        ) {
            $defenders[$dockedToWrapper->get()->getId()] = $dockedToWrapper;
        }
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
            || $ship->getWarpState());
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
