<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\KnPostArchivInterface;
use Stu\Orm\Entity\RpgPlotArchivInterface;

/**
 * @extends ObjectRepository<KnPostArchiv>
 *
 * @method null|KnPostArchivInterface find(integer $id)
 * @method KnPostArchivInterface[] findAll()
 */
interface KnPostArchivRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnPostArchivInterface;

    public function save(KnPostArchivInterface $post): void;

    public function delete(KnPostArchivInterface $post): void;

    /**
     * @return array<KnPostArchivInterface>
     */
    public function getBy(int $offset, int $limit): array;

    /**
     * @return array<KnPostArchivInterface>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<KnPostArchivInterface>
     */
    public function getByPlot(RpgPlotArchivInterface $plot, ?int $offset, ?int $limit): array;

    public function getAmount(): int;

    public function getAmountByPlot(int $plotId): int;

    public function getAmountSince(int $postId): int;

    /**
     * @return array<KnPostArchivInterface>
     */
    public function getNewerThenMark(int $mark): array;

    /**
     * @return array<KnPostArchivInterface>
     */
    public function searchByContent(string $content): array;

    public function truncateAllEntities(): void;
}
