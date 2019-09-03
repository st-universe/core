<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\WeaponInterface;

interface WeaponRepositoryInterface extends ObjectRepository
{
    public function findByModule(int $moduleId): ?WeaponInterface;
}