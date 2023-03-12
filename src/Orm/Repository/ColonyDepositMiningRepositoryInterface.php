<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\ColonyDepositMiningInterface;

/**
 * @extends ObjectRepository<ColonyDepositMining>
 */
interface ColonyDepositMiningRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyDepositMiningInterface;

    public function save(ColonyDepositMiningInterface $entity): void;
}
