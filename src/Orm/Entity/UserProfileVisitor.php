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

#[Table(name: 'stu_user_profile_visitors')]
#[Index(name: 'user_profile_visitor_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\UserProfileVisitorRepository')]
class UserProfileVisitor implements UserProfileVisitorInterface
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

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'recipient', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $opponent;

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
