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
use Stu\Component\Crew\CrewTypeEnum;
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
