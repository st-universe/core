<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\ColonyDepositMiningInterface;

/**
 * @extends ColonyDepositMining>
 */
interface ColonyDepositMiningRepositoryInterface
{
    public function prototype(): ColonyDepositMiningInterface;

    public function save(ColonyDepositMiningInterface $entity): void;
}
