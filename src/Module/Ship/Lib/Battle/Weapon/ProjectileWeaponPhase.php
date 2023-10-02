<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Message\Message;
use Stu\Module\Ship\Lib\Battle\Provider\ProjectileAttackerInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TorpedoHullInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;

//TODO unit tests
final class ProjectileWeaponPhase extends AbstractWeaponPhase implements ProjectileWeaponPhaseInterface
{
    public function fire(
        ProjectileAttackerInterface $attacker,
        array $targetPool,
        bool $isAlertRed = false
    ): array {
        $messages = [];

        for ($i = 1; $i <= $attacker->getTorpedoVolleys(); $i++) {
            if ($targetPool === []) {
                break;
            }

            $torpedo = $attacker->getTorpedo();
            $targetWrapper = $targetPool[array_rand($targetPool)];
            $target = $targetWrapper->get();

            if (
                $torpedo === null
                || !$attacker->getTorpedoState()
                || !$attacker->hasSufficientEnergy($this->getProjectileWeaponEnergyCosts())
                || $attacker->getTorpedoCount() === 0
            ) {
                break;
            }

            $torpedoName =  $torpedo->getName();

            $attacker->lowerTorpedoCount(1);
            $attacker->reduceEps($this->getProjectileWeaponEnergyCosts());

            $message = new Message($attacker->getUser()->getId(), $target->getUser()->getId());
            $messages[] = $message;

            $message->add("Die " . $attacker->getName() . " feuert einen " . $torpedoName . " auf die " . $target->getName());

            $hitchance = $attacker->getHitChance();
            if ($hitchance * (100 - $target->getEvadeChance()) < random_int(1, 10000)) {
                $message->add("Die " . $target->getName() . " wurde verfehlt");
                continue;
            }

            $isCritical = $this->isCritical($torpedo, $target->getCloakState());

            $damage_wrapper = new DamageWrapper(
                $attacker->getProjectileWeaponDamage($isCritical)
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($torpedo->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($torpedo->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);
            $this->setTorpedoHullModificator($target, $torpedo, $damage_wrapper);

            $message->addMessageMerge($this->applyDamage->damage($damage_wrapper, $targetWrapper)->getInformations());

            if ($target->isDestroyed()) {
                unset($targetPool[$target->getId()]);

                if ($isAlertRed) {
                    $this->entryCreator->addShipEntry(
                        '[b][color=red]Alarm-Rot:[/color][/b] Die ' . $target->getName() . ' (' . $target->getRump()->getName() . ') wurde in Sektor ' . $target->getSectorString() . ' von der ' . $attacker->getName() . ' zerstört',
                        $attacker->getUser()->getId()
                    );
                } else {
                    $entryMsg = sprintf(
                        'Die %s (%s) wurde in Sektor %s von der %s zerstört',
                        $target->getName(),
                        $target->getRump()->getName(),
                        $target->getSectorString(),
                        $attacker->getName()
                    );
                    if ($target->isBase()) {
                        $this->entryCreator->addStationEntry(
                            $entryMsg,
                            $attacker->getUser()->getId()
                        );
                    } else {
                        $this->entryCreator->addShipEntry(
                            $entryMsg,
                            $attacker->getUser()->getId()
                        );
                    }
                }
                $this->checkForPrestige($attacker->getUser(), $target);
                $message->add($this->shipRemover->destroy($targetWrapper));
            }
        }

        return $messages;
    }

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
                $this->entryCreator->addColonyEntry(
                    sprintf(
                        _('Das Gebäude %s auf Kolonie %s wurde von der %s zerstört'),
                        $building->getName(),
                        $target->getColony()->getName(),
                        $attacker->getName()
                    )
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

        $targetHullModule = $this->getModule($target, ShipModuleTypeEnum::MODULE_TYPE_HULL);
        if ($targetHullModule === null) {
            return;
        }

        $torpedoHull = $targetHullModule->getTorpedoHull()->get($torpedo->getId());

        if ($torpedoHull !== null) {
            $damageWrapper->setModificator($torpedoHull->getModificator());
        }
    }
}
