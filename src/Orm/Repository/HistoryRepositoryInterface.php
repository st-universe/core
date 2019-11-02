<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\HistoryInterface;

/**
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
     * @return HistoryInterface[]
     */
    public function getByType(int $typeId, int $limit): array;

    public function getAmountByType(int $typeId): int;

    public function prototype(): HistoryInterface;

    public function save(HistoryInterface $history): void;

    public function delete(HistoryInterface $history): void;
}
