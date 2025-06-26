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
use Stu\Orm\Repository\CrewTrainingRepository;

#[Table(name: 'stu_crew_training')]
#[Index(name: 'crew_training_colony_idx', columns: ['colony_id'])]
#[Index(name: 'crew_training_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: CrewTrainingRepository::class)]
class CrewTraining
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $colony_id = 0;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id')]
    private Colony $colony;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): CrewTraining
    {
        $this->user = $user;
        return $this;
    }

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function setColony(Colony $colony): CrewTraining
    {
        $this->colony = $colony;
        return $this;
    }
}
