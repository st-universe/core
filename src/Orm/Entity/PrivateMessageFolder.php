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
use Override;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Repository\PrivateMessageFolderRepository;

#[Table(name: 'stu_pm_cats')]
#[Index(name: 'user_special_idx', columns: ['user_id', 'special'])]
#[Entity(repositoryClass: PrivateMessageFolderRepository::class)]
class PrivateMessageFolder implements PrivateMessageFolderInterface
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

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): PrivateMessageFolderInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): PrivateMessageFolderInterface
    {
        $this->description = $description;
        return $this;
    }

    #[Override]
    public function getSort(): int
    {
        return $this->sort;
    }

    #[Override]
    public function setSort(int $sort): PrivateMessageFolderInterface
    {
        $this->sort = $sort;
        return $this;
    }

    #[Override]
    public function getSpecial(): PrivateMessageFolderTypeEnum
    {
        return $this->special;
    }

    #[Override]
    public function setSpecial(PrivateMessageFolderTypeEnum $special): PrivateMessageFolderInterface
    {
        $this->special = $special;
        return $this;
    }

    #[Override]
    public function isPMOutDir(): bool
    {
        return $this->getSpecial() == PrivateMessageFolderTypeEnum::SPECIAL_PMOUT;
    }

    /**
     * specifies if you can move a private message to this folder
     */
    #[Override]
    public function isDropable(): bool
    {
        return $this->getSpecial()->isDropable();
    }

    #[Override]
    public function isDeleteAble(): bool
    {
        return $this->getSpecial() === PrivateMessageFolderTypeEnum::DEFAULT_OWN;
    }

    #[Override]
    public function setDeleted(int $timestamp): PrivateMessageFolderInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    #[Override]
    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
