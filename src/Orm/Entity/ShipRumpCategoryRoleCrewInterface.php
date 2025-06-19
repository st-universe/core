<?php

namespace Stu\Orm\Entity;

use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;

interface ShipRumpCategoryRoleCrewInterface
{
    public function getId(): int;

    public function getShipRumpCategoryId(): int;

    public function getShipRumpRoleId(): SpacecraftRumpRoleEnum;

    public function getJob1Crew(): int;

    public function setJob1Crew(int $job1crew): ShipRumpCategoryRoleCrewInterface;

    public function getJob2Crew(): int;

    public function setJob2Crew(int $job2crew): ShipRumpCategoryRoleCrewInterface;

    public function getJob3Crew(): int;

    public function setJob3Crew(int $job3crew): ShipRumpCategoryRoleCrewInterface;

    public function getJob4Crew(): int;

    public function setJob4Crew(int $job4crew): ShipRumpCategoryRoleCrewInterface;

    public function getJob5Crew(): int;

    public function setJob5Crew(int $job5crew): ShipRumpCategoryRoleCrewInterface;

    public function getJob6Crew(): int;

    public function setJob6Crew(int $job6crew): ShipRumpCategoryRoleCrewInterface;

    public function getJob7Crew(): int;

    public function setJob7Crew(int $job7crew): ShipRumpCategoryRoleCrewInterface;

    public function getShiprumpRole(): ShipRumpRoleInterface;
}
