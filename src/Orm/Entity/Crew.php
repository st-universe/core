<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Crew\CrewGenderEnum;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Repository\CrewRepository;

#[Table(name: 'stu_crew')]
#[Entity(repositoryClass: CrewRepository::class)]
class Crew implements CrewInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', enumType: CrewSkillLevelEnum::class)]
    private CrewSkillLevelEnum $rank = CrewSkillLevelEnum::RECRUIT;

    #[Column(type: 'smallint', enumType: CrewGenderEnum::class)]
    private CrewGenderEnum $gender = CrewGenderEnum::MALE;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $race_id = 0;

    #[ManyToOne(targetEntity: 'CrewRace')]
    #[JoinColumn(name: 'race_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CrewRaceInterface $race;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    /**
     * @var ArrayCollection<int, CrewSkillInterface>
     */
    #[OneToMany(targetEntity: 'CrewSkill', mappedBy: 'crew', indexBy: 'position', fetch: 'EXTRA_LAZY')]
    private Collection $skills;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getRank(): CrewSkillLevelEnum
    {
        return $this->rank;
    }

    #[Override]
    public function setRank(CrewSkillLevelEnum $rank): CrewInterface
    {
        $this->rank = $rank;

        return $this;
    }

    #[Override]
    public function getGender(): CrewGenderEnum
    {
        return $this->gender;
    }

    #[Override]
    public function setGender(CrewGenderEnum $gender): CrewInterface
    {
        $this->gender = $gender;

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): CrewInterface
    {
        $this->name = $name;

        return $this;
    }

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
    public function setUser(UserInterface $user): CrewInterface
    {
        $this->user = $user;

        return $this;
    }

    #[Override]
    public function getRaceId(): int
    {
        return $this->race_id;
    }

    #[Override]
    public function setRaceId(int $raceId): CrewInterface
    {
        $this->race_id = $raceId;

        return $this;
    }

    #[Override]
    public function getRace(): CrewRaceInterface
    {
        return $this->race;
    }

    #[Override]
    public function setRace(CrewRaceInterface $crewRace): CrewInterface
    {
        $this->race = $crewRace;

        return $this;
    }

    public function getSkills(): Collection
    {
        return $this->skills;
    }

    #[Override]
    public function isSkilledAt(CrewPositionEnum $position): bool
    {
        return $this->skills->containsKey($position->value);
    }

    #[Override]
    public function __toString(): string
    {
        return sprintf('crewId: %d', $this->getId());
    }
}
