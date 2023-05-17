<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\WeaponShield;
use Stu\Orm\Entity\WeaponShieldInterface;

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

    /**
     * @return array<int>
     */
    public function getModificatorMinAndMax(): array;
}
