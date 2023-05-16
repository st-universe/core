<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\WeaponShieldInterface;
use Stu\Orm\Entity\WeaponShield;

/**
 * @extends ObjectRepository<WeaponShield>
 *
 * @method null|WeaponShieldInterface find(integer $id)
 */
interface WeaponShieldRepositoryInterface extends ObjectRepository
{
    public function prototype(): WeaponShieldInterface;

    public function save(WeaponShieldInterface $weapon): void;

    public function delete(WeaponShieldInterface $weapon): void;

    public function getByModuleAndWeapon(
        int $moduleId,
        int $weaponId
    ): ?WeaponShieldInterface;

    public function getFactionByModule($moduleid): ?WeaponShieldInterface;
}