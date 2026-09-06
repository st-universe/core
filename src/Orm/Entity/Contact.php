<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\ContactRepository;

#[Table(name: 'stu_contactlist')]
#[Index(name: 'user_pair_idx', columns: ['user_id', 'recipient'])]
#[Entity(repositoryClass: ContactRepository::class)]
#[TruncateOnGameReset]
class Contact
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $recipient = 0;

    #[Column(type: 'smallint', enumType: ContactListModeEnum::class)]
    private ContactListModeEnum $mode = ContactListModeEnum::FRIEND;

    #[Column(type: 'string', length: 50)]
    private string $comment = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[OneToMany(targetEntity: RelationPermission::class, mappedBy: 'contact')]
    private Collection $relationPermissions;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'recipient', referencedColumnName: 'id', nullable: false)]
    private User $opponent;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getRecipientId(): int
    {
        return $this->recipient;
    }

    public function getMode(): ContactListModeEnum
    {
        return $this->mode;
    }

    public function setMode(ContactListModeEnum $mode): Contact
    {
        $this->mode = $mode;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): Contact
    {
        $this->comment = $comment;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): Contact
    {
        $this->date = $date;
        return $this;
    }

    public function __construct()
    {
        $this->relationPermissions = new ArrayCollection();
    }

    public function getRelationPermissions(): Collection
    {
        return $this->relationPermissions;
    }

    public function addRelationPermission(RelationPermission $permission): self
    {
        if (!$this->relationPermissions->contains($permission)) {
            $this->relationPermissions->add($permission);
        }

        return $this;
    }

    public function hasPermissions(): bool
    {
        return !$this->relationPermissions->isEmpty();
    }

    public function hasPermission(RelationPermissionEnum $permission): bool
    {
        foreach ($this->relationPermissions as $relationPermission) {
            if ($relationPermission->getPermission() === $permission) {
                return true;
            }
        }

        return false;
    }

    public function getRecipient(): User
    {
        return $this->opponent;
    }

    public function setRecipient(User $recipient): Contact
    {
        $this->opponent = $recipient;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Contact
    {
        $this->user = $user;
        return $this;
    }

    public function isFriendly(): bool
    {
        return $this->getMode() === ContactListModeEnum::FRIEND;
    }

    public function isEnemy(): bool
    {
        return $this->getMode() === ContactListModeEnum::ENEMY;
    }
}
