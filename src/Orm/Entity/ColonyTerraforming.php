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
use Stu\Orm\Repository\ColonyTerraformingRepository;

#[Table(name: 'stu_colonies_terraforming')]
#[Index(name: 'colony_idx', columns: ['colonies_id'])]
#[Index(name: 'finished_idx', columns: ['finished'])]
#[Entity(repositoryClass: ColonyTerraformingRepository::class)]
class ColonyTerraforming
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $colonies_id = 0;

    #[Column(type: 'integer')]
    private int $field_id = 0;

    #[Column(type: 'integer')]
    private int $terraforming_id = 0;

    #[Column(type: 'integer')]
    private int $finished = 0;

    #[ManyToOne(targetEntity: Terraforming::class)]
    #[JoinColumn(name: 'terraforming_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Terraforming $terraforming;

    #[ManyToOne(targetEntity: PlanetField::class)]
    #[JoinColumn(name: 'field_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private PlanetField $field;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colonies_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Colony $colony;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyId(): int
    {
        return $this->colonies_id;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getTerraformingId(): int
    {
        return $this->terraforming_id;
    }

    public function setTerraformingId(int $terraformingId): ColonyTerraforming
    {
        $this->terraforming_id = $terraformingId;

        return $this;
    }

    public function getFinishDate(): int
    {
        return $this->finished;
    }

    public function setFinishDate(int $finishDate): ColonyTerraforming
    {
        $this->finished = $finishDate;

        return $this;
    }

    public function getTerraforming(): Terraforming
    {
        return $this->terraforming;
    }

    public function setTerraforming(Terraforming $terraforming): ColonyTerraforming
    {
        $this->terraforming = $terraforming;

        return $this;
    }

    public function getField(): PlanetField
    {
        return $this->field;
    }

    public function setField(PlanetField $planetField): ColonyTerraforming
    {
        $this->field = $planetField;

        return $this;
    }

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function setColony(Colony $colony): ColonyTerraforming
    {
        $this->colony = $colony;
        return $this;
    }

    public function getProgress(): int
    {
        $start = $this->getFinishDate() - $this->getTerraforming()->getDuration();
        return time() - $start;
    }
}
