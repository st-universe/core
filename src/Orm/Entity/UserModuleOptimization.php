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
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(name: 'stu_user_module_optimization')]
#[UniqueConstraint(name: 'user_module_optimization_idx', columns: ['user_id', 'module_id'])]
class UserModuleOptimization
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ManyToOne(targetEntity: Module::class)]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: false)]
    private Module $module;

    #[Column(name: 'improvement_steps', type: 'integer')]
    private int $improvementSteps = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;
        return $this;
    }

    public function getImprovementSteps(): int
    {
        return $this->improvementSteps;
    }

    public function setImprovementSteps(int $improvementSteps): self
    {
        $this->improvementSteps = $improvementSteps;
        return $this;
    }

}
