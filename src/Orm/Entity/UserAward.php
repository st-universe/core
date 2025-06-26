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
use Stu\Orm\Repository\UserAwardRepository;

#[Table(name: 'stu_user_award')]
#[Entity(repositoryClass: UserAwardRepository::class)]
class UserAward
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $award_id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ManyToOne(targetEntity: Award::class)]
    #[JoinColumn(name: 'award_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Award $award;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserAward
    {
        $this->user = $user;
        return $this;
    }

    public function getAwardId(): int
    {
        return $this->award_id;
    }

    public function getAward(): Award
    {
        return $this->award;
    }

    public function setAward(Award $award): UserAward
    {
        $this->award = $award;
        return $this;
    }
}
