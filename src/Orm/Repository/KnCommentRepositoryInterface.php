<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnComment;
use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\KnPostInterface;

/**
 * @extends ObjectRepository<KnComment>
 */
interface KnCommentRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<KnCommentInterface>
     */
    public function getByPost(int $postId): array;

    public function getAmountByPost(KnPostInterface $post): int;

    public function prototype(): KnCommentInterface;

    public function save(KnCommentInterface $comment): void;

    public function delete(KnCommentInterface $comment): void;

    public function truncateByUser(int $userId): void;
}
