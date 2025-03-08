<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\EnergyAttackerInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\WeaponInterface;

//TODO unit tests
final class EnergyWeaponPhase extends AbstractWeaponPhase implements EnergyWeaponPhaseInterface
{
    public const int FIRINGMODE_RANDOM = 1;
    public const int FIRINGMODE_FOCUS = 2;

    #[Override]
    public function fire(
        EnergyAttackerInterface $attacker,
        BattlePartyInterface $targetPool,
        SpacecraftAttackCauseEnum $attackCause,
        MessageCollectionInterface $messages
    ): void {

        $phaserVolleys = $attacker->getPhaserVolleys();
        for ($i = 1; $i <= $phaserVolleys; $i++) {
            if ($targetPool->isDefeated()) {
                break;
            }
            if (!$attacker->getPhaserState() || !$attacker->hasSufficientEnergy($this->getEnergyWeaponEnergyCosts())) {
                break;
            }

            $weapon = $attacker->getWeapon();

            $attacker->reduceEps($this->getEnergyWeaponEnergyCosts());

            $targetWrapper = $targetPool->getRandomActiveMember();
            if ($attacker->getFiringMode() === self::FIRINGMODE_RANDOM) {
                $targetWrapper = $targetPool->getRandomActiveMember();
            }

            $target = $targetWrapper->get();

            $message = $this->messageFactory->createMessage($attacker->getUserId(), $target->getUser()->getId());
            $messages->add($message);

            $message->add(sprintf(
                "Die %s feuert mit einem %s auf die %s",
                $attacker->getName(),
                $weapon->getName(),
                $target->getName()
            ));

            $hitChance = $this->getHitChance($attacker);
            $evadeChance = $this->getEvadeChance($target);

            if (
                $hitChance * (100 - $evadeChance) < $this->stuRandom->rand(1, 10000)
            ) {
                $message->add("Die " . $target->getName() . " wurde verfehlt");
                continue;
            }
            $isCritical = $this->isCritical($weapon, $target->isCloaked());
            $damage_wrapper = new DamageWrapper(
                $attacker->getWeaponDamage($isCritical)
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($attacker->getPhaserShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getPhaserHullDamageFactor());
            $damage_wrapper->setIsPhaserDamage(true);
            $damage_wrapper->setPirateWrath($this->getUser($attacker->getUserId()), $target);
            $this->setWeaponShieldModificator($target, $weapon, $damage_wrapper);

            $this->applyDamage->damage($damage_wrapper, $targetWrapper, $message);

            if ($target->isDestroyed()) {

                $this->checkForSpacecraftDestruction(
                    $attacker,
                    $targetWrapper,
                    $attackCause->getDestructionCause(),
                    $message
                );

                if ($weapon->getFiringMode() === self::FIRINGMODE_FOCUS) {
                    break;
                }
            }
        }
    }

    #[Override]
    public function fireAtBuilding(
        EnergyAttackerInterface $attacker,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper {
        $informations = new InformationWrapper();

        $building = $target->getBuilding();
        if ($building === null) {
            $informations->addInformation(_("Kein Gebäude vorhanden"));

            return $informations;
        }

        for ($i = 1; $i <= $attacker->getPhaserVolleys(); $i++) {
            if (!$attacker->getPhaserState() || !$attacker->hasSufficientEnergy($this->getEnergyWeaponEnergyCosts())) {
                break;
            }
            $attacker->reduceEps($this->getEnergyWeaponEnergyCosts());

            $weapon = $attacker->getWeapon();
            $informations->addInformation(sprintf(
                _("Die %s feuert mit einem %s auf das Gebäude %s auf Feld %d"),
                $attacker->getName(),
                $weapon->getName(),
                $building->getName(),
                $target->getFieldId()
            ));

            if (
                $this->getHitChance($attacker) < random_int(1, 100)
            ) {
                $informations->addInformation(_("Das Gebäude wurde verfehlt"));
                continue;
            }

            $isCritical = random_int(1, 100) <= $weapon->getCriticalChance();

            $damage_wrapper = new DamageWrapper(
                $attacker->getWeaponDamage($isCritical)
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($attacker->getPhaserShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getPhaserHullDamageFactor());
            $damage_wrapper->setIsPhaserDamage(true);


            $informations->addInformationWrapper($this->applyDamage->damageBuilding($damage_wrapper, $target, $isOrbitField));

            if ($target->getIntegrity() === 0) {
                $this->entryCreator->addEntry(
                    sprintf(
                        'Das Gebäude %s auf Kolonie %s wurde von der %s zerstört',
                        $building->getName(),
                        $target->getHost()->getName(),
                        $attacker->getName()
                    ),
                    $attacker->getUserId(),
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

    private function isCritical(WeaponInterface $weapon, bool $isTargetCloaked): bool
    {
        $critChance = $isTargetCloaked ? $weapon->getCriticalChance() * 2 : $weapon->getCriticalChance();
        return $this->stuRandom->rand(1, 100) <= $critChance;
    }

    private function setWeaponShieldModificator(
        SpacecraftInterface $target,
        WeaponInterface $weapon,
        DamageWrapper $damageWrapper
    ): void {

        $targetShieldModule = $this->getModule($target, SpacecraftModuleTypeEnum::SHIELDS);
        if ($targetShieldModule === null) {
            return;
        }

        $weaponShield = $targetShieldModule->getWeaponShield()->get($weapon->getId());

        if ($weaponShield !== null) {
            $damageWrapper->setModificator($weaponShield->getModificator());
        }
    }

    private function getEnergyWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }
}
