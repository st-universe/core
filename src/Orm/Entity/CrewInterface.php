<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Component\Crew\CrewGenderEnum;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;

interface CrewInterface
{
    public function getId(): int;

    public function getRank(): CrewSkillLevelEnum;

    public function setRank(CrewSkillLevelEnum $rank): CrewInterface;

    public function getGender(): CrewGenderEnum;

    public function setGender(CrewGenderEnum $gender): CrewInterface;

    public function getName(): string;

    public function setName(string $name): CrewInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): CrewInterface;

    public function getRaceId(): int;

    public function setRaceId(int $raceId): CrewInterface;

    public function getRace(): CrewRaceInterface;

    public function setRace(CrewRaceInterface $crewRace): CrewInterface;

    /**
     * @return ArrayCollection<int, CrewSkillInterface>
     */
    public function getSkills(): Collection;

    public function isSkilledAt(CrewPositionEnum $position): bool;
}
