<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use User;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): UserProfileVisitorInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getProfileUserId(): int
    {
        return $this->recipient;
    }

    public function setProfileUserId(int $profileUserId): UserProfileVisitorInterface
    {
        $this->recipient = $profileUserId;

        return $this;
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

    public function getUser(): User
    {
        return new User($this->getUserId());
    }

    public function getProfileUser(): User
    {
        return new User($this->getProfileUserId());
    }
}
