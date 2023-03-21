<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Lib\DamageWrapper;
use Stu\Module\Ship\Lib\Battle\Message\FightMessage;
use Stu\Module\Ship\Lib\Battle\Provider\EnergyAttackerInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\WeaponInterface;

//TODO unit tests
final class EnergyWeaponPhase extends AbstractWeaponPhase implements EnergyWeaponPhaseInterface
{
    public const FIRINGMODE_RANDOM = 1;
    public const FIRINGMODE_FOCUS = 2;

    public function fire(
        EnergyAttackerInterface $attacker,
        array $targetPool,
        bool $isAlertRed = false
    ): array {
        $fightMessages = [];

        $targetWrapper = $targetPool[array_rand($targetPool)];

        for ($i = 1; $i <= $attacker->getPhaserVolleys(); $i++) {
            if (count($targetPool) === 0) {
                break;
            }
            if (!$attacker->getPhaserState() || !$attacker->hasSufficientEnergy($this->getEnergyWeaponEnergyCosts())) {
                break;
            }

            $weapon = $attacker->getWeapon();

            $attacker->reduceEps($this->getEnergyWeaponEnergyCosts());
            if ($attacker->getFiringMode() === self::FIRINGMODE_RANDOM) {
                $targetWrapper = $targetPool[array_rand($targetPool)];
            }

            $target = $targetWrapper->get();

            $fightMessage = new FightMessage($attacker->getUser()->getId(), $target->getUser()->getId());
            $fightMessages[] = $fightMessage;

            $fightMessage->add(sprintf(
                "Die %s feuert mit einem %s auf die %s",
                $attacker->getName(),
                $$weapon->getName(),
                $target->getName()
            ));

            if (
                $attacker->getHitChance() * (100 - $target->getEvadeChance()) < rand(1, 10000)
            ) {
                $fightMessage->add("Die " . $target->getName() . " wurde verfehlt");
                continue;
            }
            $isCritical = $this->isCritical($weapon, $target->getCloakState());
            $damage_wrapper = new DamageWrapper(
                $attacker->getWeaponDamage($isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($attacker->getPhaserShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getPhaserHullDamageFactor());
            $damage_wrapper->setIsPhaserDamage(true);

            $fightMessage->addMessageMerge($this->applyDamage->damage($damage_wrapper, $targetWrapper));

            if ($target->isDestroyed()) {
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

                $targetId = $target->getId();
                $fightMessage->add($this->shipRemover->destroy($targetWrapper));

                unset($targetPool[$targetId]);

                if ($weapon->getFiringMode() === self::FIRINGMODE_FOCUS) {
                    break;
                }
            }
        }

        return $fightMessages;
    }

    public function fireAtBuilding(
        EnergyAttackerInterface $attacker,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): array {
        $msg = [];

        $building = $target->getBuilding();
        if ($building === null) {
            $msg[] = _("Kein Gebäude vorhanden");

            return $msg;
        }

        for ($i = 1; $i <= $attacker->getPhaserVolleys(); $i++) {
            if (!$attacker->getPhaserState() || !$attacker->hasSufficientEnergy($this->getEnergyWeaponEnergyCosts())) {
                break;
            }
            $attacker->reduceEps($this->getEnergyWeaponEnergyCosts());

            $weapon = $attacker->getWeapon();
            $msg[] = sprintf(_("Die %s feuert mit einem %s auf das Gebäude %s auf Feld %d"), $attacker->getName(), $weapon->getName(), $building->getName(), $target->getFieldId());

            if (
                $attacker->getHitChance() < rand(1, 100)
            ) {
                $msg[] = _("Das Gebäude wurde verfehlt");
                continue;
            }

            $isCritical = rand(1, 100) <= $weapon->getCriticalChance();

            $damage_wrapper = new DamageWrapper(
                $attacker->getWeaponDamage($isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($attacker->getPhaserShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getPhaserHullDamageFactor());
            $damage_wrapper->setIsPhaserDamage(true);

            $msg = array_merge($msg, $this->applyDamage->damageBuilding($damage_wrapper, $target, $isOrbitField));

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

        return $msg;
    }

    private function isCritical(WeaponInterface $weapon, bool $isTargetCloaked): bool
    {
        $critChance = $isTargetCloaked ? $weapon->getCriticalChance() * 2 : $weapon->getCriticalChance();
        if (rand(1, 100) <= $critChance) {
            return true;
        }
        return false;
    }

    private function getEnergyWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }
}
