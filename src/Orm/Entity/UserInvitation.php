<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\UserInvitationRepository;

#[Table(name: 'stu_user_invitations')]
#[Index(name: 'user_invitation_user_idx', columns: ['user_id'])]
#[Index(name: 'user_invitation_token_idx', columns: ['token'])]
#[Entity(repositoryClass: UserInvitationRepository::class)]
class UserInvitation
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $invited_user_id = null;

    #[Column(type: 'datetime')]
    private DateTimeInterface $date;

    #[Column(type: 'string')]
    private string $token = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): UserInvitation
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getInvitedUserId(): ?int
    {
        return $this->invited_user_id;
    }

    public function setInvitedUserId(?int $userId): UserInvitation
    {
        $this->invited_user_id = $userId;

        return $this;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): UserInvitation
    {
        $this->date = $date;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): UserInvitation
    {
        $this->token = $token;
        return $this;
    }

    public function isValid(int $ttl): bool
    {
        return $this->invited_user_id === null && time() < $this->getDate()->getTimestamp() + $ttl;
    }
}
