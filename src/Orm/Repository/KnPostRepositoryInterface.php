<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<KnPost>
 *
 * @method null|KnPost find(integer $id)
 * @method KnPost[] findAll()
 */
interface KnPostRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnPost;

    public function save(KnPost $post): void;

    public function delete(KnPost $post): void;

    /**
     * @return array<KnPost>
     */
    public function getBy(int $offset, int $limit): array;

    /**
     * @return array<KnPost>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<KnPost>
     */
    public function getByPlot(RpgPlot $plot, ?int $offset, ?int $limit): array;

    public function getAmount(): int;

    public function getAmountByPlot(int $plotId): int;

    public function getAmountSince(int $postId): int;

    /**
     * @return array<KnPost>
     */
    public function getNewerThenMark(int $mark): array;

    /**
     * @return array<KnPost>
     */
    public function searchByContent(string $content): array;

    public function truncateAllEntities(): void;

    /**
     * @return array<array{user_id: int, votes: int}>
     */
    public function getRpgVotesTop10(): array;

    public function getRpgVotesOfUser(User $user): ?int;

    public function findActiveById(int $id): ?KnPost;
}
