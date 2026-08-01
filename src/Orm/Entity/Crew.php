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
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\CrewRepository;

#[Table(name: 'stu_crew')]
#[Entity(repositoryClass: CrewRepository::class)]
#[TruncateOnGameReset]
class Crew
{
    public const int CREW_GENDER_MALE = 1;
    public const int CREW_GENDER_FEMALE = 2;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint', enumType: CrewTypeEnum::class)]
    private CrewTypeEnum $type = CrewTypeEnum::CREWMAN;

    #[Column(type: 'string', enumType: CrewSkillLevelEnum::class, options: ['default' => 'CADET'])]
    private CrewSkillLevelEnum $rank = CrewSkillLevelEnum::CADET;

    #[Column(type: 'smallint')]
    private int $gender = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $race_id = 0;

    #[ManyToOne(targetEntity: CrewRace::class)]
    #[JoinColumn(name: 'race_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CrewRace $race;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    /** @var Collection<int, CrewSkill> */
    #[OneToMany(targetEntity: CrewSkill::class, mappedBy: 'crew', indexBy: 'position', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $skills;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): CrewTypeEnum
    {
        return $this->type;
    }

    public function setType(CrewTypeEnum $type): Crew
    {
        $this->type = $type;

        return $this;
    }

    public function getRank(): CrewSkillLevelEnum
    {
        return $this->rank;
    }

    public function setRank(CrewSkillLevelEnum $rank): Crew
    {
        $this->rank = $rank;

        return $this;
    }

    /** @return Collection<int, CrewSkill> */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function isSkilledAt(CrewTypeEnum $position): bool
    {
        return $this->skills->containsKey($position->value);
    }

    public function getSkillAt(CrewTypeEnum $position): ?CrewSkill
    {
        return $this->skills->get($position->value);
    }

    public function getExpertiseSum(): int
    {
        $sum = 0;
        foreach ($this->skills as $skill) {
            $sum += $skill->getExpertise();
        }

        return $sum;
    }

    public function getHighestSkillExpertise(): int
    {
        $highestExpertise = 0;
        foreach ($this->skills as $skill) {
            $highestExpertise = max($highestExpertise, $skill->getExpertise());
        }

        return $highestExpertise;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function setGender(int $gender): Crew
    {
        $this->gender = $gender;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Crew
    {
        $this->name = $name;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Crew
    {
        $this->user = $user;

        return $this;
    }

    public function getRaceId(): int
    {
        return $this->race_id;
    }

    public function setRaceId(int $raceId): Crew
    {
        $this->race_id = $raceId;

        return $this;
    }

    public function getGenderShort(): string
    {
        if ($this->getGender() == self::CREW_GENDER_MALE) {
            return 'm';
        }
        return 'w';
    }

    public function getRace(): CrewRace
    {
        return $this->race;
    }

    public function setRace(CrewRace $crewRace): Crew
    {
        $this->race = $crewRace;

        return $this;
    }

    public function __toString(): string
    {
        return isset($this->id)
            ? sprintf('crewId: %d', $this->id)
            : sprintf('crew: %s (%s)', $this->name, $this->getGenderShort());
    }
}
