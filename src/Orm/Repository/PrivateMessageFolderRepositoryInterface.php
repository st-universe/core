<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<PrivateMessageFolder>
 *
 * @method null|PrivateMessageFolderInterface find(integer $id)
 * @method PrivateMessageFolderInterface[] findAll()
 */
interface PrivateMessageFolderRepositoryInterface extends ObjectRepository
{
    public function prototype(): PrivateMessageFolderInterface;

    public function save(PrivateMessageFolderInterface $post): void;

    public function delete(PrivateMessageFolderInterface $post): void;

    /**
     * @return array<PrivateMessageFolderInterface>
     */
    public function getOrderedByUser(int $userId): array;

    public function getByUserAndSpecial(int $userId, PrivateMessageFolderTypeEnum $folderType): ?PrivateMessageFolderInterface;

    public function getMaxOrderIdByUser(UserInterface $user): int;

    public function truncateAllNonNpcFolders(): void;
}
