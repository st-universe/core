<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceBoardPost;

/**
 * @extends ObjectRepository<AllianceBoardPost>
 *
 * @method null|AllianceBoardPost find(integer $id)
 */
interface AllianceBoardPostRepositoryInterface extends ObjectRepository
{
    public function getRecentByBoard(int $boardId): ?AllianceBoardPost;

    public function getRecentByTopic(int $topicId): ?AllianceBoardPost;

    public function getAmountByBoard(int $boardId): int;

    public function getAmountByTopic(int $topicId): int;

    /**
     * @return list<AllianceBoardPost>
     */
    public function getByTopic(int $topicId, int $limit, int $offset): array;

    public function prototype(): AllianceBoardPost;

    public function save(AllianceBoardPost $post): void;

    public function delete(AllianceBoardPost $post): void;
}
