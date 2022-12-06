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
    private $email = '';

    /** @Column(type="string", length=255, nullable=true) */
    private $mobile;

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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): BlockedUserInterface
    {
        $this->email = $email;
        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): BlockedUserInterface
    {
        $this->mobile = $mobile;
        return $this;
    }
}
