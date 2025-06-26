<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnCommentArchiv;
use Stu\Orm\Entity\KnPostArchiv;

/**
 * @extends ObjectRepository<KnCommentArchiv>
 */
interface KnCommentArchivRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<KnCommentArchiv>
     */
    public function getByPost(int $postId): array;

    public function getAmountByPost(KnPostArchiv $post): int;

    public function prototype(): KnCommentArchiv;

    public function save(KnCommentArchiv $comment): void;

    public function delete(KnCommentArchiv $comment): void;

    public function truncateByUser(int $userId): void;
}
