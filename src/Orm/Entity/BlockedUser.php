<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BlockedUserRepository")
 * @Table(
 *     name="stu_blocked_user"
 * )
 **/
class BlockedUser implements BlockedUserInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     */
    private $user_id;

    /** @Column(type="integer") */
    private $time = 0;

    /** @Column(type="string", length=255) */
    private $emailHash = '';

    /** @Column(type="string", length=255, nullable=true) */
    private $mobileHash;

    public function getId(): int
    {
        return $this->user_id;
    }

    public function setId(int $userId): BlockedUserInterface
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): BlockedUserInterface
    {
        $this->time = $time;
        return $this;
    }

    public function getEmailHash(): string
    {
        return $this->emailHash;
    }

    public function setEmailHash(string $emailHash): BlockedUserInterface
    {
        $this->emailHash = $emailHash;
        return $this;
    }

    public function getMobileHash(): ?string
    {
        return $this->mobileHash;
    }

    public function setMobileHash(?string $mobileHash): BlockedUserInterface
    {
        $this->mobileHash = $mobileHash;
        return $this;
    }
}
