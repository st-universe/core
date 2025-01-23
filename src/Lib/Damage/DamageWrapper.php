<?php

namespace Stu\Lib\Damage;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Pirate\Component\PirateWrathManager;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

class DamageWrapper
{
    private bool $isCrit = false;
    private bool $isShieldPenetration = false;
    private int $modificator = 100;
    private ?int $pirateWrath = null;

    /** @var null|array<SpacecraftSystemTypeEnum> */
    private ?array $targetSystemTypes = null;

    public function __construct(private float $netDamage) {}

    private int $hull_damage_factor = 100;


    public function setHullDamageFactor(int $value): DamageWrapper
    {
        $this->hull_damage_factor = $value;

        return $this;
    }


    public function getHullDamageFactor(): int
    {
        return $this->hull_damage_factor;
    }

    public function setCrit(bool $isCrit): DamageWrapper
    {
        $this->isCrit = $isCrit;

        return $this;
    }

    public function isCrit(): bool
    {
        return $this->isCrit;
    }

    public function setShieldPenetration(bool $isShieldPenetration): void
    {
        $this->isShieldPenetration = $isShieldPenetration;
    }

    public function isShieldPenetration(): bool
    {
        return $this->isShieldPenetration;
    }

    private int $shield_damage_factor = 100;


    public function setShieldDamageFactor(int $value): DamageWrapper
    {
        $this->shield_damage_factor = $value;

        return $this;
    }


    public function getShieldDamageFactor(): int
    {
        return $this->shield_damage_factor;
    }

    private bool $is_phaser_damage = false;


    public function setIsPhaserDamage(bool $value): void
    {
        $this->is_phaser_damage = $value;
    }


    public function getIsPhaserDamage(): bool
    {
        return $this->is_phaser_damage;
    }

    private bool $is_torpedo_damage = false;


    public function setIsTorpedoDamage(bool $value): void
    {
        $this->is_torpedo_damage = $value;
    }


    public function getIsTorpedoDamage(): bool
    {
        return $this->is_torpedo_damage;
    }

    public function setNetDamage(float $value): void
    {
        $this->netDamage = $value;
    }

    public function getNetDamage(): float
    {
        return $this->netDamage;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $value): void
    {
        $this->modificator = $value;
    }

    public function setPirateWrath(UserInterface $attacker, SpacecraftInterface $target): void
    {
        if ($attacker->getId() !== UserEnum::USER_NPC_KAZON) {
            return;
        }

        $pirateWrath = $target->getUser()->getPirateWrath();
        if ($pirateWrath === null) {
            return;
        }

        $this->pirateWrath = $pirateWrath->getWrath();
    }

    public function canDamageSystem(SpacecraftSystemTypeEnum $type): bool
    {
        return $this->targetSystemTypes === null
            || in_array($type, $this->targetSystemTypes);
    }

    /** @param array<SpacecraftSystemTypeEnum> $targetSystemTypes */
    public function setTargetSystemTypes(array $targetSystemTypes): DamageWrapper
    {
        $this->targetSystemTypes = $targetSystemTypes;

        return $this;
    }

    public function getDamageRelative(ColonyInterface|SpacecraftInterface $target, DamageModeEnum $mode): float
    {
        if ($target instanceof ColonyInterface) {
            if ($mode === DamageModeEnum::HULL) {
                return $this->calculateDamageBuilding();
            }
            return $this->calculateDamageColonyShields($target);
        }
        if ($mode === DamageModeEnum::HULL) {
            return $this->calculateDamageHull();
        }

        return $this->calculateDamageShields($target);
    }


    private function calculateDamageShields(SpacecraftInterface $target): float
    {
        $netDamage = $this->getNetDamage();
        $netDamage = $this->mindPirateWrath($netDamage);

        $targetShields = $target->getShield();

        $grossModificator = $this->getShieldDamageFactor() / 100;
        if ($this->getIsPhaserDamage() === true) {
            $grossModificator = $grossModificator * $this->modificator / 100;
        }

        $neededNetDamageForShields = min($netDamage, (int)ceil($targetShields / $grossModificator));
        $grossDamage = min($targetShields, $neededNetDamageForShields * $grossModificator);

        if ($neededNetDamageForShields >= $netDamage) {
            $this->setNetDamage(0);
        } else {
            $this->setNetDamage($netDamage - $neededNetDamageForShields);
        }

        return $grossDamage;
    }


    private function calculateDamageColonyShields(ColonyInterface $target): float
    {
        $damage = round($this->getNetDamage() / 100 * $this->getShieldDamageFactor());

        if ($damage < $target->getShields()) {
            $this->setNetDamage(0);
        } else {
            $this->setNetDamage(round($damage - $target->getShields() / $this->getShieldDamageFactor() * 100));
        }
        return $damage;
    }


    private function calculateDamageHull(): float
    {
        $damage = round($this->getNetDamage() / 100 * $this->getHullDamageFactor());
        $damage = $this->mindPirateWrath($damage);

        if ($this->getIsTorpedoDamage() === true) {
            $damage = round($damage * ($this->getModificator() / 100));
        }
        return $damage;
    }


    private function calculateDamageBuilding(): float
    {
        return round($this->getNetDamage() / 100 * $this->getHullDamageFactor());
    }

    private function mindPirateWrath(float $damage): float
    {
        if ($this->pirateWrath === null) {
            return $damage;
        }

        return round($damage / PirateWrathManager::DEFAULT_WRATH * $this->pirateWrath);
    }
}
