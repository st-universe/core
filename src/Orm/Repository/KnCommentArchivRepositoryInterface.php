<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnCommentArchiv;
use Stu\Orm\Entity\KnCommentArchivInterface;
use Stu\Orm\Entity\KnPostArchivInterface;

/**
 * @extends ObjectRepository<KnCommentArchiv>
 */
interface KnCommentArchivRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<KnCommentArchivInterface>
     */
    public function getByPost(int $postId): array;

    public function getAmountByPost(KnPostArchivInterface $post): int;

    public function prototype(): KnCommentArchivInterface;

    public function save(KnCommentArchivInterface $comment): void;

    public function delete(KnCommentArchivInterface $comment): void;

    public function truncateByUser(int $userId): void;
}
