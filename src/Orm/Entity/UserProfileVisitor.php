<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\UserProfileVisitorRepository;

#[Table(name: 'stu_user_profile_visitors')]
#[Index(name: 'user_profile_visitor_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: UserProfileVisitorRepository::class)]
class UserProfileVisitor
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $recipient = 0;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'recipient', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $opponent;

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

    public function setDate(int $date): UserProfileVisitor
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserProfileVisitor
    {
        $this->user = $user;
        return $this;
    }

    public function getProfileUser(): User
    {
        return $this->opponent;
    }

    public function setProfileUser(User $profileUser): UserProfileVisitor
    {
        $this->opponent = $profileUser;
        return $this;
    }
}
