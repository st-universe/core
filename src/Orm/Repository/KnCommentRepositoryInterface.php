<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnComment;
use Stu\Orm\Entity\KnPost;

/**
 * @extends ObjectRepository<KnComment>
 */
interface KnCommentRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<KnComment>
     */
    public function getByPost(int $postId): array;

    public function getAmountByPost(KnPost $post): int;

    public function prototype(): KnComment;

    public function save(KnComment $comment): void;

    public function delete(KnComment $comment): void;

    public function truncateByUser(int $userId): void;
}
