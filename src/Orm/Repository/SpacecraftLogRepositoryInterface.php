<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SpacecraftLog;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<SpacecraftLog>
 */
interface SpacecraftLogRepositoryInterface extends ObjectRepository
{
    public function prototype(): SpacecraftLog;

    public function save(SpacecraftLog $spacecraftLog): void;

    public function delete(SpacecraftLog $spacecraftLog): void;

    /** @return array<int, SpacecraftLog> */
    public function getBySpacecraftId(int $spacecraftId, bool $includePrivate = true): array;

    /** @return array<int, SpacecraftLog> */
    public function getBySpacecraftIdUntil(int $spacecraftId, int $date, bool $includePrivate = true): array;

    public function hasVisibleLogbook(int $spacecraftId): bool;

    /** @return array<int, array{spacecraftId: int, name: string, rumpId: ?int, scanDate: ?int, logs: array<int, SpacecraftLog>}> */
    public function getGroupedLogbooksForProfile(User $profileUser, User $visitor): array;
}
