<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyInterface;
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

    public function save(ColonyInterface $colony): void;

    public function delete(ColonyInterface $colony): void;

    public function getAmountByUser(UserInterface $user, int $colonyType): int;

    /**
     * @return array<int, ColonyInterface>
     */
    public function getStartingByFaction(int $factionId): array;

    public function getByPosition(StarSystemMapInterface $sysmap): ?ColonyInterface;

    /**
     * @return list<ColonyInterface>
     */
    public function getForeignColoniesInBroadcastRange(
        StarSystemMapInterface $systemMap,
        UserInterface $user
    ): array;

    /**
     * @return iterable<ColonyInterface>
     */
    public function getByBatchGroup(int $batchGroup, int $batchGroupCount): iterable;

    /**
     * @return iterable<ColonyInterface>
     */
    public function getColonized(): iterable;

    /**
     * @return array<array{colonyid: int, classid: int, nameandsector: string}>
     */
    public function getColonyListForRenderFragment(UserInterface $user): array;

    /**
     * @return array<array{user_id: int, commodity_id: int, sum: int}>
     */
    public function getColoniesNetWorth(): array;

    /**
     * @return array<array{user_id: int, satisfied: int}>
     */
    public function getSatisfiedWorkerTop10(): array;
}
