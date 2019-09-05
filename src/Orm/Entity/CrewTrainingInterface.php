<?php

namespace Stu\Orm\Entity;

use Colony;
use User;

interface CrewTrainingInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): CrewTrainingInterface;

    public function getColonyId(): int;

    public function setColonyId(int $colonyId): CrewTrainingInterface;

    public function getUser(): User;

    public function getColony(): Colony;
}