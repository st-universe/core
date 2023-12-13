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
use Stu\Module\Message\Lib\ContactListModeEnum;

#[Table(name: 'stu_contactlist')]
#[Index(name: 'user_pair_idx', columns: ['user_id', 'recipient'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ContactRepository')]
class Contact implements ContactInterface
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

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'recipient', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $opponent;

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

    public function setMode(ContactListModeEnum $mode): ContactInterface
    {
        $this->mode = $mode;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): ContactInterface
    {
        $this->comment = $comment;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): ContactInterface
    {
        $this->date = $date;
        return $this;
    }

    public function getRecipient(): UserInterface
    {
        return $this->opponent;
    }

    public function setRecipient(UserInterface $recipient): ContactInterface
    {
        $this->opponent = $recipient;
        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): ContactInterface
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
