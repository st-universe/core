<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Override;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Battle\Provider\ProjectileAttackerInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;

//TODO unit tests
final class ProjectileWeaponPhase extends AbstractWeaponPhase implements ProjectileWeaponPhaseInterface
{
    #[Override]
    public function fire(
        ProjectileAttackerInterface $attacker,
        BattlePartyInterface $targetPool,
        ShipAttackCauseEnum $attackCause,
        MessageCollectionInterface $messages
    ): void {

        for ($i = 1; $i <= $attacker->getTorpedoVolleys(); $i++) {
            if ($targetPool->isDefeated()) {
                break;
            }

            $torpedo = $attacker->getTorpedo();
            $targetWrapper = $targetPool->getRandomActiveMember();
            $target = $targetWrapper->get();

            if (
                $torpedo === null
                || !$attacker->getTorpedoState()
                || !$attacker->hasSufficientEnergy($this->getProjectileWeaponEnergyCosts())
                || $attacker->getTorpedoCount() === 0
            ) {
                break;
            }

            $isCritical = $this->isCritical($torpedo, $target->getCloakState());
            $netDamage = $attacker->getProjectileWeaponDamage($isCritical);

            $damage_wrapper = new DamageWrapper($netDamage);

            $torpedoName =  $torpedo->getName();

            $attacker->lowerTorpedoCount(1);
            $attacker->reduceEps($this->getProjectileWeaponEnergyCosts());

            $message = $this->messageFactory->createMessage($attacker->getUser()->getId(), $target->getUser()->getId());
            $messages->add($message);

            $message->add("Die " . $attacker->getName() . " feuert einen " . $torpedoName . " auf die " . $target->getName());

            $hitchance = $attacker->getHitChance();
            if ($hitchance * (100 - $target->getEvadeChance()) < random_int(1, 10000)) {
                $message->add("Die " . $target->getName() . " wurde verfehlt");
                continue;
            }

            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldPenetration($attacker->isShieldPenetration());
            $damage_wrapper->setShieldDamageFactor($torpedo->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($torpedo->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);
            $damage_wrapper->setPirateWrath($attacker->getUser(), $target);
            $this->setTorpedoHullModificator($target, $torpedo, $damage_wrapper);

            $this->applyDamage->damage($damage_wrapper, $targetWrapper, $message);

            if ($target->isDestroyed()) {
                $this->checkForShipDestruction(
                    $attacker,
                    $targetWrapper,
                    $attackCause->getDestructionCause(),
                    $message
                );
            }
        }
    }

    #[Override]
    public function fireAtBuilding(
        ProjectileAttackerInterface $attacker,
        PlanetFieldInterface $target,
        bool $isOrbitField,
        int &$antiParticleCount
    ): InformationWrapper {
        $informations = new InformationWrapper();

        $building = $target->getBuilding();
        if ($building === null) {
            return $informations;
        }

        for ($i = 1; $i <= $attacker->getTorpedoVolleys(); $i++) {
            $torpedo = $attacker->getTorpedo();

            if (
                $torpedo === null
                || !$attacker->getTorpedoState()
                || !$attacker->hasSufficientEnergy($this->getProjectileWeaponEnergyCosts())
            ) {
                break;
            }

            $attacker->lowerTorpedoCount(1);
            $attacker->reduceEps($this->getProjectileWeaponEnergyCosts());

            $informations->addInformation(sprintf(
                _("Die %s feuert einen %s auf das Gebäude %s auf Feld %d"),
                $attacker->getName(),
                $torpedo->getName(),
                $building->getName(),
                $target->getFieldId()
            ));

            if ($antiParticleCount > 0) {
                $antiParticleCount--;
                $informations->addInformation("Der Torpedo wurde vom orbitalem Torpedoabwehrsystem abgefangen");
                continue;
            }
            if ($attacker->getHitChance() < random_int(1, 100)) {
                $informations->addInformation("Das Gebäude wurde verfehlt");
                continue;
            }
            $isCritical = random_int(1, 100) <= $torpedo->getCriticalChance();
            $damage_wrapper = new DamageWrapper(
                $attacker->getProjectileWeaponDamage($isCritical)
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($torpedo->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($torpedo->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);

            $informations->addInformationWrapper($this->applyDamage->damageBuilding($damage_wrapper, $target, $isOrbitField));

            if ($target->getIntegrity() === 0) {
                $this->entryCreator->addEntry(
                    sprintf(
                        _('Das Gebäude %s auf Kolonie %s wurde von der %s zerstört'),
                        $building->getName(),
                        $target->getHost()->getName(),
                        $attacker->getName()
                    ),
                    $attacker->getUser()->getId(),
                    $target->getHost()
                );

                $this->buildingManager->remove($target);
                break;
            }
            //deactivate if high damage
            elseif ($target->hasHighDamage()) {
                $this->buildingManager->deactivate($target);
            }
        }

        return $informations;
    }

    private function getProjectileWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }

    private function isCritical(TorpedoTypeInterface $torpedo, bool $isTargetCloaked): bool
    {
        $critChance = $isTargetCloaked ? $torpedo->getCriticalChance() * 2 : $torpedo->getCriticalChance();
        return random_int(1, 100) <= $critChance;
    }

    private function setTorpedoHullModificator(
        ShipInterface $target,
        TorpedoTypeInterface $torpedo,
        DamageWrapper $damageWrapper
    ): void {

        $targetHullModule = $this->getModule($target, ShipModuleTypeEnum::HULL);
        if ($targetHullModule === null) {
            return;
        }

        $torpedoHull = $targetHullModule->getTorpedoHull()->get($torpedo->getId());

        if ($torpedoHull !== null) {
            $damageWrapper->setModificator($torpedoHull->getModificator());
        }
    }
}
