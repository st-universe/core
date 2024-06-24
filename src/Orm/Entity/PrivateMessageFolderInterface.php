<?php

namespace Stu\Orm\Entity;

use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;

interface PrivateMessageFolderInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): PrivateMessageFolderInterface;

    public function getDescription(): string;

    public function setDescription(string $description): PrivateMessageFolderInterface;

    public function getSort(): int;

    public function setSort(int $sort): PrivateMessageFolderInterface;

    public function getSpecial(): PrivateMessageFolderTypeEnum;

    public function setSpecial(PrivateMessageFolderTypeEnum $special): PrivateMessageFolderInterface;

    public function isPMOutDir(): bool;

    public function isDropable(): bool;

    public function isDeleteAble(): bool;

    public function setDeleted(int $timestamp): PrivateMessageFolderInterface;

    public function isDeleted(): bool;
}
