<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\WeaponShield;

/**
 * @extends ObjectRepository<WeaponShield>
 *
 * @method null|WeaponShield find(integer $id)
 */
interface WeaponShieldRepositoryInterface extends ObjectRepository
{
    public function prototype(): WeaponShield;

    public function save(WeaponShield $weapon): void;

    public function delete(WeaponShield $weapon): void;

    /**
     * @return array<int>
     */
    public function getModificatorMinAndMax(): array;
}
