<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\ColonyDepositMiningInterface;

interface ColonyDepositMiningRepositoryInterface
{
    public function prototype(): ColonyDepositMiningInterface;

    public function save(ColonyDepositMiningInterface $entity): void;
}
