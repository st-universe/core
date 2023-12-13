<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Table(name: 'stu_terraforming')]
#[Index(name: 'terraforming_research_idx', columns: ['research_id'])]
#[UniqueConstraint(name: 'field_transformation_idx', columns: ['v_feld', 'z_feld'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\TerraformingRepository')]
class Terraforming implements TerraformingInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'integer')]
    private int $ecost = 0;

    #[Column(type: 'integer')]
    private int $v_feld = 0;

    #[Column(type: 'integer')]
    private int $z_feld = 0;

    #[Column(type: 'integer')]
    private int $duration = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $research_id = null;

    /**
     * @var ArrayCollection<int, TerraformingCostInterface>
     */
    #[OneToMany(targetEntity: 'TerraformingCost', mappedBy: 'terraforming')]
    private Collection $costs;

    public function __construct()
    {
        $this->costs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): TerraformingInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getEnergyCosts(): int
    {
        return $this->ecost;
    }

    public function setEnergyCosts(int $energyCosts): TerraformingInterface
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    public function getFromFieldTypeId(): int
    {
        return $this->v_feld;
    }

    public function setFromFieldTypeId(int $fromFieldTypeId): TerraformingInterface
    {
        $this->v_feld = $fromFieldTypeId;

        return $this;
    }

    public function getToFieldTypeId(): int
    {
        return $this->z_feld;
    }

    public function setToFieldTypeId(int $toFieldTypeId): TerraformingInterface
    {
        $this->z_feld = $toFieldTypeId;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): TerraformingInterface
    {
        $this->duration = $duration;

        return $this;
    }

    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    public function setResearchId(?int $researchId): TerraformingInterface
    {
        $this->research_id = $researchId;
        return $this;
    }

    public function getCosts(): Collection
    {
        return $this->costs;
    }
}
