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
use Stu\Orm\Repository\UserTutorialRepository;

#[Table(name: 'stu_user_tutorial')]
#[Entity(repositoryClass: UserTutorialRepository::class)]
class UserTutorial implements UserTutorialInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: 'User', inversedBy: 'tutorials')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private UserInterface $user;

    #[Column(type: 'string', length: 255)]
    private string $module;

    #[Column(type: 'integer')]
    private int $step;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): UserTutorialInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getModule(): string
    {
        return $this->module;
    }

    #[Override]
    public function setModule(string $module): UserTutorialInterface
    {
        $this->module = $module;
        return $this;
    }

    #[Override]
    public function getStep(): int
    {
        return $this->step;
    }

    #[Override]
    public function setStep(int $step): UserTutorialInterface
    {
        $this->step = $step;
        return $this;
    }
}