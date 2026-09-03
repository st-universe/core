<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\UserCrewRaceRepository;

#[Table(name: 'stu_user_crew_race')]
#[Entity(repositoryClass: UserCrewRaceRepository::class)]
class UserCrewRace
{
    #[Id]
    #[ManyToOne(targetEntity: CrewRace::class)]
    #[JoinColumn(name: 'crew_race_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private CrewRace $crewRace;

    #[Id]
    #[Column(name: 'user_id', type: 'integer')]
    private int $user_id;

    public function getCrewRace(): CrewRace
    {
        return $this->crewRace;
    }

    public function setCrewRace(CrewRace $crewRace): UserCrewRace
    {
        $this->crewRace = $crewRace;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): UserCrewRace
    {
        $this->user_id = $userId;

        return $this;
    }
}
