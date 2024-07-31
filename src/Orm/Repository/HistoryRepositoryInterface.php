<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Orm\Entity\History;
use Stu\Orm\Entity\HistoryInterface;

/**
 * @extends ObjectRepository<History>
 *
 * @method null|HistoryInterface find(integer $id)
 * @method HistoryInterface[] findAll()
 */
interface HistoryRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<HistoryInterface>
     */
    public function getRecent(): array;

    /**
     * @return array<HistoryInterface>
     */
    public function getRecentWithoutPirate(): array;

    /**
     *
     * @return array<HistoryInterface>
     */
    public function getByTypeAndSearch(HistoryTypeEnum $type, int $limit): array;

    /**
     *
     * @return array<HistoryInterface>
     */
    public function getByTypeAndSearchWithoutPirate(HistoryTypeEnum $type, int $limit): array;

    public function getAmountByType(int $typeId): int;

    public function prototype(): HistoryInterface;

    public function save(HistoryInterface $history): void;

    public function delete(HistoryInterface $history): void;

    public function truncateAllEntities(): void;

    public function getSumDestroyedByUser(int $source_user, int $target_user): int;
}
