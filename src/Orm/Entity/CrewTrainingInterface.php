<?php

namespace Stu\Orm\Entity;

interface CrewTrainingInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getColonyId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): CrewTrainingInterface;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): CrewTrainingInterface;
}