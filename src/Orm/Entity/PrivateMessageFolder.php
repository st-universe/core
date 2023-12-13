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
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;

#[Table(name: 'stu_pm_cats')]
#[Index(name: 'user_special_idx', columns: ['user_id', 'special'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\PrivateMessageFolderRepository')]
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

    #[Column(type: 'smallint')]
    private int $special = PrivateMessageFolderSpecialEnum::PM_DEFAULT_OWN;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): PrivateMessageFolderInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): PrivateMessageFolderInterface
    {
        $this->description = $description;
        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): PrivateMessageFolderInterface
    {
        $this->sort = $sort;
        return $this;
    }

    public function getSpecial(): int
    {
        return $this->special;
    }

    public function setSpecial(int $special): PrivateMessageFolderInterface
    {
        $this->special = $special;
        return $this;
    }

    public function isPMOutDir(): bool
    {
        return $this->getSpecial() == PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT;
    }

    /**
     * specifies if you can move a private message to this folder
     */
    public function isDropable(): bool
    {
        switch ($this->getSpecial()) {
            case PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP:
            case PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION:
            case PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY:
            case PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE:
            case PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM:
            case PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT:
                return false;
        }
        return true;
    }

    public function isDeleteAble(): bool
    {
        return $this->getSpecial() === 0;
    }

    public function setDeleted(int $timestamp): PrivateMessageFolderInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
