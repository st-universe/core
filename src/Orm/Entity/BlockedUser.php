<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\BlockedUserRepository;

#[Table(name: 'stu_blocked_user')]
#[Entity(repositoryClass: BlockedUserRepository::class)]
class BlockedUser
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

    public function getId(): int
    {
        return $this->user_id;
    }

    public function setId(int $userId): BlockedUser
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): BlockedUser
    {
        $this->time = $time;
        return $this;
    }

    public function getEmailHash(): string
    {
        return $this->email_hash;
    }

    public function setEmailHash(string $emailHash): BlockedUser
    {
        $this->email_hash = $emailHash;
        return $this;
    }

    public function getMobileHash(): ?string
    {
        return $this->mobile_hash;
    }

    public function setMobileHash(?string $mobileHash): BlockedUser
    {
        $this->mobile_hash = $mobileHash;
        return $this;
    }
}
