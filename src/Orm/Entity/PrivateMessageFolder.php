<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Doctrine\ORM\Mapping\Index;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PrivateMessageFolderRepository")
 * @Table(
 *     name="stu_pm_cats",
 *     indexes={
 *         @Index(name="user_special_idx", columns={"user_id","special"})
 *     }
 * )
 **/
class PrivateMessageFolder implements PrivateMessageFolderInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $description = '';

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $sort = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $special = PrivateMessageFolderSpecialEnum::PM_DEFAULT_OWN;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $deleted;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

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

    public function getCategoryCount(): int
    {
        // @todo refactor
        global $container;

        return $container->get(PrivateMessageRepositoryInterface::class)->getAmountByFolder(
            $this->getId(),
        );
    }

    public function getCategoryCountNew(): int
    {
        // @todo refactor
        global $container;

        return $container->get(PrivateMessageRepositoryInterface::class)->getNewAmountByFolder(
            $this->getId(),
        );
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
