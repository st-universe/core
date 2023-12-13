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

#[Table(name: 'stu_user_award')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\UserAwardRepository')]
class UserAward implements UserAwardInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $award_id;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'Award')]
    #[JoinColumn(name: 'award_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AwardInterface $award;

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

    public function getAwardId(): int
    {
        return $this->award_id;
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
