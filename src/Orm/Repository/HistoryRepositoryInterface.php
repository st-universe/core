<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\HistoryInterface;

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
}