<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserProfileVisitorRepository")
 * @Table(
 *     name="stu_user_profile_visitors",
 *     indexes={
 *         @Index(name="user_profile_visitor_user_idx", columns={"user_id"})
 *     }
 * )
 **/
class UserProfileVisitor implements UserProfileVisitorInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $recipient = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $date = 0;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var UserInterface
     *
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
