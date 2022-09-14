<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Player\UserAwardEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserAwardRepository")
 * @Table(
 *     name="stu_user_award"
 * )
 **/
class UserAward implements UserAwardInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer", nullable=true) */
    private $type = 0;

    /** @Column(type="integer") */
    private $award_id;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="Award")
     * @JoinColumn(name="award_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $award;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): UserAwardInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getAward(): AwardInterface
    {
        return $this->award;
    }

    public function setAward(AwardInterface $award): UserAwardInterface
    {
        $this->award = $award;
        return $this;
    }
}
