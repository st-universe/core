<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Repository\PrivateMessageFolderRepository;

#[Table(name: 'stu_pm_cats')]
#[Index(name: 'user_special_idx', columns: ['user_id', 'special'])]
#[Entity(repositoryClass: PrivateMessageFolderRepository::class)]
class PrivateMessageFolder
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'smallint')]
    private int $sort = 0;

    #[Column(type: 'smallint', length: 1, enumType: PrivateMessageFolderTypeEnum::class)]
    private PrivateMessageFolderTypeEnum $special = PrivateMessageFolderTypeEnum::DEFAULT_OWN;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): PrivateMessageFolder
    {
        $this->user = $user;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): PrivateMessageFolder
    {
        $this->description = $description;
        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): PrivateMessageFolder
    {
        $this->sort = $sort;
        return $this;
    }

    public function getSpecial(): PrivateMessageFolderTypeEnum
    {
        return $this->special;
    }

    public function setSpecial(PrivateMessageFolderTypeEnum $special): PrivateMessageFolder
    {
        $this->special = $special;
        return $this;
    }

    public function isPMOutDir(): bool
    {
        return $this->getSpecial() == PrivateMessageFolderTypeEnum::SPECIAL_PMOUT;
    }

    /**
     * specifies if you can move a private message to this folder
     */
    public function isDropable(): bool
    {
        return $this->getSpecial()->isDropable();
    }

    public function isDeleteAble(): bool
    {
        return $this->getSpecial() === PrivateMessageFolderTypeEnum::DEFAULT_OWN;
    }

    public function setDeleted(int $timestamp): PrivateMessageFolder
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
