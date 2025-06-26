<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\Colony;

/**
 * @extends ObjectRepository<ColonyDepositMining>
 */
interface ColonyDepositMiningRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyDepositMining;

    public function save(ColonyDepositMining $entity): void;

    /** @return array<int, ColonyDepositMining> */
    public function getCurrentUserDepositMinings(Colony $colony): array;
}
