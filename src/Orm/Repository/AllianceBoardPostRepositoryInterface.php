<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceBoardPostInterface;

/**
 * @extends ObjectRepository<AllianceBoardPost>
 *
 * @method null|AllianceBoardPostInterface find(integer $id)
 */
interface AllianceBoardPostRepositoryInterface extends ObjectRepository

{
    public function getRecentByBoard(int $boardId): ?AllianceBoardPostInterface;

    public function getRecentByTopic(int $topicId): ?AllianceBoardPostInterface;

    public function getAmountByBoard(int $boardId): int;

    public function getAmountByTopic(int $topicId): int;

    /**
     * @return AllianceBoardPostInterface[]
     */
    public function getByTopic(int $topicId, int $limit, int $offset): array;

    public function prototype(): AllianceBoardPostInterface;

    public function save(AllianceBoardPostInterface $post): void;

    public function delete(AllianceBoardPostInterface $post): void;
}
