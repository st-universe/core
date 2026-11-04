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
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Table(name: 'stu_away_mission_field')]
#[Entity]
#[UniqueConstraint(name: 'away_mission_field_idx', columns: ['mission_id', 'field_id'])]
class AwayMissionField
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: AwayMission::class)]
    #[JoinColumn(name: 'mission_id', referencedColumnName: 'id', nullable: false)]
    private AwayMission $mission;

    #[Column(type: 'integer')]
    private int $fieldId = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMission(): AwayMission
    {
        return $this->mission;
    }

    public function setMission(AwayMission $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    public function getFieldId(): int
    {
        return $this->fieldId;
    }

    public function setFieldId(int $fieldId): self
    {
        $this->fieldId = $fieldId;

        return $this;
    }
}
