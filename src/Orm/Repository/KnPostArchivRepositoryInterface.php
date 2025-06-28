<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\RpgPlotArchiv;

/**
 * @extends ObjectRepository<KnPostArchiv>
 *
 * @method null|KnPostArchiv find(integer $id)
 * @method KnPostArchiv[] findAll()
 */
interface KnPostArchivRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnPostArchiv;

    public function save(KnPostArchiv $post): void;

    public function delete(KnPostArchiv $post): void;

    /**
     * @return array<KnPostArchiv>
     */
    public function getBy(int $offset, int $limit): array;

    /**
     * @return array<KnPostArchiv>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<KnPostArchiv>
     */
    public function getByPlot(RpgPlotArchiv $plot, ?int $offset, ?int $limit): array;

    /**
     * @return array<KnPostArchiv>
     */
    public function getByVersion(string $version, int $offset, int $limit): array;

    /**
     * @return array<KnPostArchiv>
     */
    public function getByVersionWithPlots(string $version, int $offset, int $limit): array;

    /**
     * @param array<int> $plotIds
     * @return array<int, RpgPlotArchiv>
     */
    public function getPlotsByIds(array $plotIds): array;

    /**
     * @return array<string>
     */
    public function getAvailableVersions(): array;

    public function getAmount(): int;

    public function getAmountByPlot(int $plotId): int;

    public function getAmountByVersion(string $version): int;

    public function getAmountSince(int $postId): int;

    /**
     * @return array<KnPostArchiv>
     */
    public function getNewerThenMark(int $mark): array;

    /**
     * @return array<KnPostArchiv>
     */
    public function searchByContent(string $content): array;

    public function truncateAllEntities(): void;

    public function findByFormerId(int $formerId): ?KnPostArchiv;

    /**
     * @return array<KnPostArchiv>
     */
    public function getByPlotFormerId(int $plotFormerId, ?int $offset, ?int $limit): array;
}
