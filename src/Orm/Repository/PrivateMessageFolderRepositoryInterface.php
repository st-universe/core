<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<PrivateMessageFolder>
 *
 * @method null|PrivateMessageFolder find(integer $id)
 * @method PrivateMessageFolder[] findAll()
 */
interface PrivateMessageFolderRepositoryInterface extends ObjectRepository
{
    public function prototype(): PrivateMessageFolder;

    public function save(PrivateMessageFolder $post): void;

    public function delete(PrivateMessageFolder $post): void;

    /**
     * @return array<PrivateMessageFolder>
     */
    public function getOrderedByUser(User $user): array;

    public function getByUserAndSpecial(int $userId, PrivateMessageFolderTypeEnum $folderType): ?PrivateMessageFolder;

    public function getMaxOrderIdByUser(User $user): int;

    public function truncateAllNonNpcFolders(): void;
}
