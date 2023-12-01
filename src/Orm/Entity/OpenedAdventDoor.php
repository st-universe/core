<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\OpenedAdventDoorRepository")
 * @Table(
 *     name="stu_opened_advent_door"
 * )
 **/
class OpenedAdventDoor implements OpenedAdventDoorInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $user_id;

    /**
     * @Column(type="integer")
     *
     */
    private int $day;

    /**
     * @Column(type="integer")
     *
     */
    private int $year;

    /**
     * @Column(type="integer")
     *
     */
    private int $time;


    public function getId(): int
    {
        return $this->id;
    }

    public function setUserId(int $userId): OpenedAdventDoorInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function setDay(int $day): OpenedAdventDoorInterface
    {
        $this->day = $day;

        return $this;
    }

    public function setYear(int $year): OpenedAdventDoorInterface
    {
        $this->year = $year;

        return $this;
    }
    public function setTime(int $time): OpenedAdventDoorInterface
    {
        $this->time = $time;

        return $this;
    }
}
