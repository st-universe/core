<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\UserRefererRepository;

#[Table(name: 'stu_user_referer')]
#[Entity(repositoryClass: UserRefererRepository::class)]
#[TruncateOnGameReset]
class UserReferer
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[OneToOne(targetEntity: UserRegistration::class, inversedBy: 'referer')]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private UserRegistration $userRegistration;

    #[Column(type: 'text')]
    private string $referer;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserRegistration(): UserRegistration
    {
        return $this->userRegistration;
    }

    public function setUserRegistration(UserRegistration $registration): UserReferer
    {
        $this->userRegistration = $registration;
        return $this;
    }

    public function getReferer(): string
    {
        return $this->referer;
    }

    public function setReferer(string $referer): UserReferer
    {
        $this->referer = $referer;
        return $this;
    }
}
