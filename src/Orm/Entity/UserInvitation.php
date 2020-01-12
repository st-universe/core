<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserInvitationRepository")
 * @Table(
 *     name="stu_user_invitations",
 *     indexes={
 *         @Index(name="user_invitation_user_idx", columns={"user_id"}),
 *         @Index(name="user_invitation_token_idx", columns={"token"})
 *     }
 * )
 **/
class UserInvitation implements UserInvitationInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer", nullable=true) */
    private $invited_user_id;

    /** @Column(type="datetime") */
    private $date;

    /** @Column(type="string") */
    private $token = '';

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="invited_user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $invited_user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): UserInvitationInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): UserInvitationInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getInvitedUser(): ?UserInterface
    {
        return $this->invited_user;
    }

    public function setInvitedUser(?UserInterface $user): UserInvitationInterface
    {
        $this->invited_user = $user;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): UserInvitationInterface
    {
        $this->token = $token;
        return $this;
    }

    public function isValid(int $ttl): bool {
        return $this->getInvitedUser() === null && time() < $this->getDate()->getTimestamp() + $ttl;
    }
}
