<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Repository\UserCrewRankRepository;

#[Table(name: 'stu_user_crew_rank')]
#[Entity(repositoryClass: UserCrewRankRepository::class)]
class UserCrewRank
{
    #[Id]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id')]
    private User $user;

    #[Id]
    #[Column(type: 'string', enumType: CrewSkillLevelEnum::class)]
    private CrewSkillLevelEnum $rank;

    #[Column(type: 'string', length: 64)]
    private string $name = '';

    public function setUser(User $user): UserCrewRank
    {
        $this->user = $user;

        return $this;
    }

    public function getRank(): CrewSkillLevelEnum
    {
        return $this->rank;
    }

    public function setRank(CrewSkillLevelEnum $rank): UserCrewRank
    {
        $this->rank = $rank;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): UserCrewRank
    {
        $this->name = $name;

        return $this;
    }
}
