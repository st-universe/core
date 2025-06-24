<?php

namespace Stu\Orm\Entity;

interface SpacecraftRumpBaseValuesInterface
{
    public function getEvadeChance(): int;

    public function getHitChance(): int;

    public function getModuleLevel(): int;

    public function getBaseCrew(): int;

    public function getBaseEps(): int;

    public function getBaseReactor(): int;

    public function getBaseHull(): int;

    public function getBaseShield(): int;

    public function getBaseDamage(): int;

    public function getBaseSensorRange(): int;

    public function getBaseWarpDrive(): int;

    public function getSpecialSlots(): int;
}
