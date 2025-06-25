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
use Override;
use Stu\Orm\Repository\UserProfileVisitorRepository;

#[Table(name: 'stu_user_profile_visitors')]
#[Index(name: 'user_profile_visitor_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: UserProfileVisitorRepository::class)]
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

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'recipient', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $opponent;

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
    public function getProfileUserId(): int
    {
        return $this->recipient;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): UserProfileVisitorInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): UserProfileVisitorInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getProfileUser(): UserInterface
    {
        return $this->opponent;
    }

    #[Override]
    public function setProfileUser(UserInterface $profileUser): UserProfileVisitorInterface
    {
        $this->opponent = $profileUser;
        return $this;
    }
}
