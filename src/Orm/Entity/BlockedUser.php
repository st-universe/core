<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\BlockedUserRepository;

#[Table(name: 'stu_blocked_user')]
#[Entity(repositoryClass: BlockedUserRepository::class)]
class BlockedUser implements BlockedUserInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $time = 0;

    #[Column(type: 'string', length: 255)]
    private string $email_hash = '';

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $mobile_hash = null;

    #[Override]
    public function getId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function setId(int $userId): BlockedUserInterface
    {
        $this->user_id = $userId;
        return $this;
    }

    #[Override]
    public function getTime(): int
    {
        return $this->time;
    }

    #[Override]
    public function setTime(int $time): BlockedUserInterface
    {
        $this->time = $time;
        return $this;
    }

    #[Override]
    public function getEmailHash(): string
    {
        return $this->email_hash;
    }

    #[Override]
    public function setEmailHash(string $emailHash): BlockedUserInterface
    {
        $this->email_hash = $emailHash;
        return $this;
    }

    #[Override]
    public function getMobileHash(): ?string
    {
        return $this->mobile_hash;
    }

    #[Override]
    public function setMobileHash(?string $mobileHash): BlockedUserInterface
    {
        $this->mobile_hash = $mobileHash;
        return $this;
    }
}
