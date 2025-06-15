<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\ColonyDepositMiningInterface;
use Stu\Orm\Entity\ColonyInterface;

/**
 * @extends ObjectRepository<ColonyDepositMining>
 */
interface ColonyDepositMiningRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyDepositMiningInterface;

    public function save(ColonyDepositMiningInterface $entity): void;

    /** @return array<int, ColonyDepositMiningInterface> */
    public function getCurrentUserDepositMinings(ColonyInterface $colony): array;
}
