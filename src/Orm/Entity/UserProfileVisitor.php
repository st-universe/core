<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserProfileVisitorRepository")
 * @Table(
 *     name="stu_user_profile_visitors",
 *     indexes={
 *         @Index(name="user_idx", columns={"user_id"})
 *     }
 * )
 **/
class UserProfileVisitor implements UserProfileVisitorInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $recipient = 0;

    /** @Column(type="integer") */
    private $date = 0;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="recipient", referencedColumnName="id", onDelete="CASCADE")
     */
    private $opponent;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getProfileUserId(): int
    {
        return $this->recipient;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): UserProfileVisitorInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): UserProfileVisitorInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getProfileUser(): UserInterface
    {
        return $this->opponent;
    }

    public function setProfileUser(UserInterface $profileUser): UserProfileVisitorInterface
    {
        $this->opponent = $profileUser;
        return $this;
    }
}
