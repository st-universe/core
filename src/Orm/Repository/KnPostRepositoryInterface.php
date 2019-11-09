<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;

/**
 * @method null|KnPostInterface find(integer $id)
 * @method KnPostInterface[] findAll()
 */
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
    public function getByPlot(RpgPlotInterface $plot, ?int $offset, ?int $limit): array;

    public function getAmount(): int;

    public function getAmountByPlot(int $plotId): int;

    public function getAmountSince(int $postId): int;

    public function getNewerThenMark(int $mark): array;
}
