<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\UserInterface;

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
     * @return array<KnPostInterface>
     */
    public function getBy(int $offset, int $limit): array;

    /**
     * @return array<KnPostInterface>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<KnPostInterface>
     */
    public function getByPlot(RpgPlotInterface $plot, ?int $offset, ?int $limit): array;

    public function getAmount(): int;

    public function getAmountByPlot(int $plotId): int;

    public function getAmountSince(int $postId): int;

    /**
     * @return array<KnPostInterface>
     */
    public function getNewerThenMark(int $mark): array;

    /**
     * @return array<KnPostInterface>
     */
    public function searchByContent(string $content): array;

    public function truncateAllEntities(): void;

    /**
     * @return array<array{user_id: int, votes: int}>
     */
    public function getRpgVotesTop10(): array;

    public function getRpgVotesOfUser(UserInterface $user): ?int;

    public function findActiveById(int $id): ?KnPostInterface;
}
