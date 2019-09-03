<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPostInterface;

interface KnPostRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnPostInterface;

    public function save(KnPostInterface $post): void;

    public function delete(KnPostInterface $post): void;

    /**
     * @return KnPostInterface[]
     */
    public function getBy(int $offset, int $limit): array;

    /**
     * @return KnPostInterface[]
     */
    public function getByUser(int $userId): array;

    /**
     * @return KnPostInterface[]
     */
    public function getByPlot(int $plotId, ?int $offset, ?int $limit): array;

    public function getNewerThenMark(int $mark): array;
}