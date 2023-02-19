<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;

/**
 * @extends ObjectRepository<KnPost>
 *
 * @method null|KnPostInterface find(integer $id)
 * @method KnPostInterface[] findAll()
 */
interface KnPostRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnPostInterface;

    public function save(KnPostInterface $post): void;

    public function delete(KnPostInterface $post): void;

    /**
     * @return list<KnPostInterface>
     */
    public function getBy(int $offset, int $limit): array;

    /**
     * @return list<KnPostInterface>
     */
    public function getByUser(int $userId): array;

    /**
     * @return list<KnPostInterface>
     */
    public function getByPlot(RpgPlotInterface $plot, ?int $offset, ?int $limit): array;

    public function getAmount(): int;

    public function getAmountByPlot(int $plotId): int;

    public function getAmountSince(int $postId): int;

    /**
     * @return list<KnPostInterface>
     */
    public function getNewerThenMark(int $mark): array;

    /**
     * @return list<KnPostInterface>
     */
    public function searchByContent(string $content): array;
}
