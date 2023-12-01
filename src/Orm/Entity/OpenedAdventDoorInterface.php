<?php

namespace Stu\Orm\Entity;

interface OpenedAdventDoorInterface
{
    public function getId(): int;

    public function setUserId(int $userId): OpenedAdventDoorInterface;

    public function setDay(int $day): OpenedAdventDoorInterface;

    public function setYear(int $year): OpenedAdventDoorInterface;

    public function setTime(int $time): OpenedAdventDoorInterface;
}
