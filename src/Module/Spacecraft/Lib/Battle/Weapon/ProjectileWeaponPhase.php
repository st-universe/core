<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\ProjectileAttackerInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoType;

//TODO unit tests
final class ProjectileWeaponPhase extends AbstractWeaponPhase implements ProjectileWeaponPhaseInterface
{
    #[Override]
    public function fire(
        ProjectileAttackerInterface $attacker,
        BattlePartyInterface $targetPool,
        SpacecraftAttackCauseEnum $attackCause,
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

            if ($attacker->isAvoidingHullHits($target)) {
                break;
            }

            $isCritical = $this->isCritical($torpedo, $target->isCloaked());
            $netDamage = $attacker->getProjectileWeaponDamage($isCritical);

            $damage_wrapper = new DamageWrapper($netDamage);

            $torpedoName =  $torpedo->getName();

            $attacker->lowerTorpedoCount(1);
            $attacker->reduceEps($this->getProjectileWeaponEnergyCosts());

            $message = $this->messageFactory->createMessage($attacker->getUserId(), $target->getUser()->getId());
            $messages->add($message);

            $message->add("Die " . $attacker->getName() . " feuert einen " . $torpedoName . " auf die " . $target->getName());

            $hitchance = $this->getHitChance($attacker);
            $evadeChance = $this->getEvadeChance($targetWrapper);
            if ($hitchance * (100 - $evadeChance) < random_int(1, 10000)) {
                $message->add("Die " . $target->getName() . " wurde verfehlt");
                continue;
            }

            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldPenetration($attacker->isShieldPenetration());
            $damage_wrapper->setShieldDamageFactor($torpedo->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($torpedo->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);
            $damage_wrapper->setPirateWrath($this->getUser($attacker->getUserId()), $target);
            $this->setTorpedoHullModificator($target, $torpedo, $damage_wrapper);

            $this->applyDamage->damage($damage_wrapper, $targetWrapper, $message);

            if ($target->getCondition()->isDestroyed()) {
                $this->checkForSpacecraftDestruction(
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
        PlanetField $target,
        bool $isOrbitField,
        int &$antiParticleCount
    ): InformationWrapper {

        $informations = new InformationWrapper();

        $host = $target->getHost();
        if (!$host instanceof Colony) {
            return $informations;
        }

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

            $informations->addInformationWrapper($this->applyBuildingDamage->damageBuilding($damage_wrapper, $target, $isOrbitField));

            if ($target->getIntegrity() === 0) {
                $this->entryCreator->addEntry(
                    sprintf(
                        _('Das Gebäude %s auf Kolonie %s wurde von der %s zerstört'),
                        $building->getName(),
                        $host->getName(),
                        $attacker->getName()
                    ),
                    $attacker->getUserId(),
                    $host
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

    private function isCritical(TorpedoType $torpedo, bool $isTargetCloaked): bool
    {
        $critChance = $isTargetCloaked ? $torpedo->getCriticalChance() * 2 : $torpedo->getCriticalChance();
        return random_int(1, 100) <= $critChance;
    }

    private function setTorpedoHullModificator(
        Spacecraft $target,
        TorpedoType $torpedo,
        DamageWrapper $damageWrapper
    ): void {

        $targetHullModule = $this->getModule($target, SpacecraftModuleTypeEnum::HULL);
        if ($targetHullModule === null) {
            return;
        }

        $torpedoHull = $targetHullModule->getTorpedoHull()->get($torpedo->getId());

        if ($torpedoHull !== null) {
            $damageWrapper->setModificator($torpedoHull->getModificator());
        }
    }
}
