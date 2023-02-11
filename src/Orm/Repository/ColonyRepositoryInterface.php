<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Colony>
 *
 * @method null|ColonyInterface find(integer $id)
 * @method ColonyInterface[] findAll()
 */
interface ColonyRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyInterface;

    public function save(ColonyInterface $post): void;

    public function delete(ColonyInterface $post): void;

    public function getAmountByUser(UserInterface $user, int $colonyType): int;

    /**
     * @return ColonyInterface[]
     */
    public function getStartingByFaction(int $factionId): iterable;

    public function getByPosition(StarSystemMapInterface $sysmap): ?ColonyInterface;

    /**
     * @return ColonyInterface[]
     */
    public function getForeignColoniesInBroadcastRange(ShipInterface $ship): array;

    /**
     * @return ColonyInterface[]
     */
    public function getByTick(int $tick, int $batchGroup, int $batchGroupCount): iterable;

    /**
     * @return ColonyInterface[]
     */
    public function getColonized(): iterable;
}
