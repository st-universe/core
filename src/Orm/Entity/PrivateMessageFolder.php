<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="string") */
    private $description = '';

    /** @Column(type="smallint") */
    private $sort = 0;

    /** @Column(type="smallint") */
    private $special = 0;

    /**
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
        return (int) DB()->query("SELECT COUNT(id) FROM stu_pms WHERE cat_id=" . $this->getId(), 1);
    }

    public function getCategoryCountNew(): int
    {
        return (int) DB()->query("SELECT COUNT(id) FROM stu_pms WHERE new=1 AND cat_id=" . $this->getId(), 1);
    }

    public function appendToSorting(): void
    {
        $sort = (int) DB()->query("SELECT MAX(sort) FROM stu_pm_cats WHERE user_id=" . $this->getUserId(), 1);
        $this->setSort($sort + 1);
    }

    public function isPMOutDir(): bool
    {
        return $this->getSpecial() == PM_SPECIAL_PMOUT;
    }

    public function isDropable(): bool
    {
        switch ($this->getSpecial()) {
            case PM_SPECIAL_SHIP:
            case PM_SPECIAL_COLONY:
            case PM_SPECIAL_TRADE:
            case PM_SPECIAL_PMOUT:
                return false;
        }
        return true;
    }

    public function isDeleteAble(): bool
    {
        return $this->getSpecial() === 0;
    }

    public function truncate(): void
    {
        DB()->query("DELETE FROM stu_pms WHERE cat_id=" . $this->getId());
    }
}
