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
use Override;
use Stu\Orm\Repository\UserAwardRepository;

#[Table(name: 'stu_user_award')]
#[Entity(repositoryClass: UserAwardRepository::class)]
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

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: Award::class)]
    #[JoinColumn(name: 'award_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AwardInterface $award;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): UserAwardInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getAwardId(): int
    {
        return $this->award_id;
    }

    #[Override]
    public function getAward(): AwardInterface
    {
        return $this->award;
    }

    #[Override]
    public function setAward(AwardInterface $award): UserAwardInterface
    {
        $this->award = $award;
        return $this;
    }
}
