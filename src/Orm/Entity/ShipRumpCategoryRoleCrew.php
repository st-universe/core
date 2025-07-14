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
use Stu\Component\Crew\CrewTypeEnum;
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

    public function getCrewForPosition(CrewTypeEnum $type): int
    {
        return match ($type) {
            CrewTypeEnum::COMMAND => $this->job_1_crew,
            CrewTypeEnum::SECURITY => $this->job_2_crew,
            CrewTypeEnum::SCIENCE => $this->job_3_crew,
            CrewTypeEnum::TECHNICAL => $this->job_4_crew,
            CrewTypeEnum::NAVIGATION => $this->job_5_crew,
            CrewTypeEnum::CREWMAN => $this->job_6_crew,
            CrewTypeEnum::CAPTAIN => $this->job_7_crew
        };
    }

    public function getCrewSumForPositionsExceptCrewman(): int
    {
        return $this->job_1_crew
            + $this->job_2_crew
            + $this->job_3_crew
            + $this->job_4_crew
            + $this->job_5_crew
            + $this->job_7_crew;
    }
}
