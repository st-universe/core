<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\OpenedAdventDoorRepository;

#[Table(name: 'stu_opened_advent_door')]
#[Entity(repositoryClass: OpenedAdventDoorRepository::class)]
class OpenedAdventDoor
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $day;

    #[Column(type: 'integer')]
    private int $year;

    #[Column(type: 'integer')]
    private int $time;


    public function getId(): int
    {
        return $this->id;
    }

    public function setUserId(int $userId): OpenedAdventDoor
    {
        $this->user_id = $userId;

        return $this;
    }

    public function setDay(int $day): OpenedAdventDoor
    {
        $this->day = $day;

        return $this;
    }

    public function setYear(int $year): OpenedAdventDoor
    {
        $this->year = $year;

        return $this;
    }
    public function setTime(int $time): OpenedAdventDoor
    {
        $this->time = $time;

        return $this;
    }
}
