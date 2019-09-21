<?php

namespace Stu\Orm\Entity;

use User;

interface CrewTrainingInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): CrewTrainingInterface;

    public function getColonyId(): int;

    public function getUser(): User;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): CrewTrainingInterface;
}