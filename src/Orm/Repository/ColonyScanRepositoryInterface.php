<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyScan;

/**
 * @method null|ColonyScan find(integer $id)
 * @extends ObjectRepository<ColonyScan>
 */
interface ColonyScanRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyScan;

    public function save(ColonyScan $post): void;

    public function delete(ColonyScan $post): void;

    /**
     * @return list<ColonyScan>
     */
    public function getByUser(int $userId): array;

    public function truncateByUserId(ColonyScan $userId): void;

    /** @return array<array<string, int>> */
    public function getSurface(int $colonyId): array;

    public function getSurfaceArray(int $id): string;

    public function getSurfaceWidth(int $id): int;

    public function truncateAllColonyScans(): void;
}
