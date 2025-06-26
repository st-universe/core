<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepository;

#[Table(name: 'stu_rumps_cat_role_crew')]
#[Index(name: 'ship_rump_category_role_idx', columns: ['rump_category_id', 'rump_role_id'])]
#[Entity(repositoryClass: ShipRumpCategoryRoleCrewRepository::class)]
class ShipRumpCategoryRoleCrew
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_category_id = 0;

    #[Column(type: 'integer', enumType: SpacecraftRumpRoleEnum::class)]
    private SpacecraftRumpRoleEnum $rump_role_id;

    #[Column(type: 'smallint')]
    private int $job_1_crew = 0;

    #[Column(type: 'smallint')]
    private int $job_2_crew = 0;

    #[Column(type: 'smallint')]
    private int $job_3_crew = 0;

    #[Column(type: 'smallint')]
    private int $job_4_crew = 0;

    #[Column(type: 'smallint')]
    private int $job_5_crew = 0;

    #[Column(type: 'smallint')]
    private int $job_6_crew = 0;

    #[Column(type: 'smallint')]
    private int $job_7_crew = 0;

    #[ManyToOne(targetEntity: ShipRumpRole::class)]
    #[JoinColumn(name: 'rump_role_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipRumpRole $shiprumpRole;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipRumpCategoryId(): int
    {
        return $this->rump_category_id;
    }

    public function getShipRumpRoleId(): SpacecraftRumpRoleEnum
    {
        return $this->rump_role_id;
    }

    public function getJob1Crew(): int
    {
        return $this->job_1_crew;
    }

    public function setJob1Crew(int $job1crew): ShipRumpCategoryRoleCrew
    {
        $this->job_1_crew = $job1crew;

        return $this;
    }

    public function getJob2Crew(): int
    {
        return $this->job_2_crew;
    }

    public function setJob2Crew(int $job2crew): ShipRumpCategoryRoleCrew
    {
        $this->job_2_crew = $job2crew;

        return $this;
    }

    public function getJob3Crew(): int
    {
        return $this->job_3_crew;
    }

    public function setJob3Crew(int $job3crew): ShipRumpCategoryRoleCrew
    {
        $this->job_3_crew = $job3crew;

        return $this;
    }

    public function getJob4Crew(): int
    {
        return $this->job_4_crew;
    }

    public function setJob4Crew(int $job4crew): ShipRumpCategoryRoleCrew
    {
        $this->job_4_crew = $job4crew;

        return $this;
    }

    public function getJob5Crew(): int
    {
        return $this->job_5_crew;
    }

    public function setJob5Crew(int $job5crew): ShipRumpCategoryRoleCrew
    {
        $this->job_5_crew = $job5crew;

        return $this;
    }

    public function getJob6Crew(): int
    {
        return $this->job_6_crew;
    }

    public function setJob6Crew(int $job6crew): ShipRumpCategoryRoleCrew
    {
        $this->job_6_crew = $job6crew;

        return $this;
    }

    public function getJob7Crew(): int
    {
        return $this->job_7_crew;
    }

    public function setJob7Crew(int $job7crew): ShipRumpCategoryRoleCrew
    {
        $this->job_7_crew = $job7crew;

        return $this;
    }

    public function getShiprumpRole(): ShipRumpRole
    {
        return $this->shiprumpRole;
    }
}
