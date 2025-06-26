<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Orm\Entity\History;

/**
 * @extends ObjectRepository<History>
 *
 * @method null|History find(integer $id)
 * @method History[] findAll()
 */
interface HistoryRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<History>
     */
    public function getRecent(): array;

    /**
     * @return array<History>
     */
    public function getRecentWithoutPirate(): array;

    /**
     *
     * @return array<History>
     */
    public function getByTypeAndSearch(HistoryTypeEnum $type, int $limit): array;

    /**
     *
     * @return array<History>
     */
    public function getByTypeAndSearchWithoutPirate(HistoryTypeEnum $type, int $limit): array;

    public function getAmountByType(int $typeId): int;

    public function prototype(): History;

    public function save(History $history): void;

    public function delete(History $history): void;

    public function truncateAllEntities(): void;

    public function getSumDestroyedByUser(int $source_user, int $target_user): int;
}
