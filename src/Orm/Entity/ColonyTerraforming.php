<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\ColonyTerraformingRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_colonies_terraforming')]
#[Index(name: 'colony_idx', columns: ['colonies_id'])]
#[Index(name: 'finished_idx', columns: ['finished'])]
#[Entity(repositoryClass: ColonyTerraformingRepository::class)]
class ColonyTerraforming implements ColonyTerraformingInterface
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

    #[ManyToOne(targetEntity: 'Terraforming')]
    #[JoinColumn(name: 'terraforming_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TerraformingInterface $terraforming;

    #[ManyToOne(targetEntity: 'PlanetField')]
    #[JoinColumn(name: 'field_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private PlanetFieldInterface $field;

    #[ManyToOne(targetEntity: 'Colony')]
    #[JoinColumn(name: 'colonies_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyInterface $colony;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getColonyId(): int
    {
        return $this->colonies_id;
    }

    #[Override]
    public function getFieldId(): int
    {
        return $this->field_id;
    }

    #[Override]
    public function getTerraformingId(): int
    {
        return $this->terraforming_id;
    }

    #[Override]
    public function setTerraformingId(int $terraformingId): ColonyTerraformingInterface
    {
        $this->terraforming_id = $terraformingId;

        return $this;
    }

    #[Override]
    public function getFinishDate(): int
    {
        return $this->finished;
    }

    #[Override]
    public function setFinishDate(int $finishDate): ColonyTerraformingInterface
    {
        $this->finished = $finishDate;

        return $this;
    }

    #[Override]
    public function getTerraforming(): TerraformingInterface
    {
        return $this->terraforming;
    }

    #[Override]
    public function setTerraforming(TerraformingInterface $terraforming): ColonyTerraformingInterface
    {
        $this->terraforming = $terraforming;

        return $this;
    }

    #[Override]
    public function getField(): PlanetFieldInterface
    {
        return $this->field;
    }

    #[Override]
    public function setField(PlanetFieldInterface $planetField): ColonyTerraformingInterface
    {
        $this->field = $planetField;

        return $this;
    }

    #[Override]
    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function setColony(ColonyInterface $colony): ColonyTerraformingInterface
    {
        $this->colony = $colony;
        return $this;
    }

    #[Override]
    public function getProgress(): int
    {
        $start = $this->getFinishDate() - $this->getTerraforming()->getDuration();
        return time() - $start;
    }
}
