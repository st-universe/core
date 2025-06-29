<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Colony>
 *
 * @method null|Colony find(integer $id)
 * @method Colony[] findAll()
 */
interface ColonyRepositoryInterface extends ObjectRepository
{
    public function prototype(): Colony;

    public function save(Colony $colony): void;

    public function delete(Colony $colony): void;

    public function getAmountByUser(User $user, int $colonyType): int;

    /**
     * @return array<int, Colony>
     */
    public function getStartingByFaction(int $factionId): array;

    /**
     * @return array<Colony>
     */
    public function getForeignColoniesInBroadcastRange(
        StarSystemMap $systemMap,
        User $user
    ): array;

    /**
     * @return array<Colony>
     */
    public function getByBatchGroup(int $batchGroup, int $batchGroupCount): array;

    /**
     * @return array<Colony>
     */
    public function getColonized(): array;

    /**
     * @return array<Colony>
     */
    public function getPirateTargets(SpacecraftWrapperInterface $wrapper): array;

    /**
     * @return array<array{user_id: int, commodity_id: int, sum: int}>
     */
    public function getColoniesNetWorth(): array;

    /**
     * @return array<array{user_id: int, commodity_id: int, sum: int}>
     */
    public function getColoniesProductionNetWorth(): array;

    /**
     * @return array<array{user_id: int, satisfied: int}>
     */
    public function getSatisfiedWorkerTop10(): array;

    public function getClosestColonizableColonyDistance(SpacecraftWrapperInterface $wrapper): ?int;
}
