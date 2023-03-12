<?php

namespace Stu\Orm\Entity;

interface CrewInterface
{
    public function getId(): int;

    public function getType(): int;

    public function setType(int $type): CrewInterface;

    public function getGender(): int;

    public function setGender(int $gender): CrewInterface;

    public function getName(): string;

    public function setName(string $name): CrewInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): CrewInterface;

    public function getRaceId(): int;

    public function setRaceId(int $raceId): CrewInterface;

    public function getGenderShort(): string;

    public function getTypeDescription(): string;

    public function getRace(): CrewRaceInterface;

    public function setRace(CrewRaceInterface $crewRace): CrewInterface;
}
