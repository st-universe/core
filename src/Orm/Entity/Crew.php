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
use Stu\Component\Crew\CrewEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\CrewRepository")
 * @Table(
 *     name="stu_crew",
 *     indexes={
 *     }
 * )
 **/
class Crew implements CrewInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $type = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $gender = 0;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $name = '';

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $race_id = 0;

    /**
     * @var CrewRaceInterface
     *
     * @ManyToOne(targetEntity="CrewRace")
     * @JoinColumn(name="race_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $race;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): CrewInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function setGender(int $gender): CrewInterface
    {
        $this->gender = $gender;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CrewInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): CrewInterface
    {
        $this->user = $user;

        return $this;
    }

    public function getRaceId(): int
    {
        return $this->race_id;
    }

    public function setRaceId(int $raceId): CrewInterface
    {
        $this->race_id = $raceId;

        return $this;
    }

    public function getGenderShort(): string
    {
        if ($this->getGender() == CrewEnum::CREW_GENDER_MALE) {
            return 'm';
        }
        return 'w';
    }

    public function getTypeDescription(): string
    {
        switch ($this->getType()) {
            case CrewEnum::CREW_TYPE_CREWMAN:
                return "Crewman";
            case CrewEnum::CREW_TYPE_SECURITY:
                return "Sicherheit";
            case CrewEnum::CREW_TYPE_SCIENCE:
                return "Wissenschaft";
            case CrewEnum::CREW_TYPE_TECHNICAL:
                return "Technik";
            case CrewEnum::CREW_TYPE_NAVIGATION:
                return "Navigation";
            case CrewEnum::CREW_TYPE_COMMAND:
                return "Kommando";
        }
        return '';
    }

    public function getRace(): CrewRaceInterface
    {
        return $this->race;
    }

    public function setRace(CrewRaceInterface $crewRace): CrewInterface
    {
        $this->race = $crewRace;

        return $this;
    }
}
