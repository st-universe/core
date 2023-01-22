<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\History;
use Stu\Orm\Entity\HistoryInterface;

/**
 * @extends ObjectRepository<History>
 *
 * @method null|HistoryInterface find(integer $id)
 * @method array|HistoryInterface[] findAll()
 */
interface HistoryRepositoryInterface extends ObjectRepository
{
    /**
     * @return HistoryInterface[]
     */
    public function getRecent(): array;

    /**
     * @param false|string $search
     *
     * @return HistoryInterface[]
     */
    public function getByTypeAndSearch(int $typeId, int $limit, $search): array;

    public function getAmountByType(int $typeId): int;

    public function prototype(): HistoryInterface;

    public function save(HistoryInterface $history): void;

    public function delete(HistoryInterface $history): void;
}
