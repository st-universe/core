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
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\BuildingUpgradeRepository;

#[Table(name: 'stu_buildings_upgrades')]
#[Index(name: 'upgrade_from_research_idx', columns: ['upgrade_from', 'research_id'])]
#[Entity(repositoryClass: BuildingUpgradeRepository::class)]
class BuildingUpgrade
{
    #[Id]
    #[Column(type: 'bigint')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $upgrade_from = 0;

    #[Column(type: 'integer')]
    private int $upgrade_to = 0;

    #[Column(type: 'integer')]
    private int $research_id = 0;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'integer')]
    private int $energy_cost = 0;

    /**
     * @var ArrayCollection<int, BuildingUpgradeCost>
     */
    #[OneToMany(targetEntity: BuildingUpgradeCost::class, mappedBy: 'upgrade')]
    private Collection $upgradeCosts;

    #[ManyToOne(targetEntity: Building::class)]
    #[JoinColumn(name: 'upgrade_to', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Building $upgradeToBuilding;

    /**
     * @var Building
     */
    #[ManyToOne(targetEntity: Building::class)]
    #[JoinColumn(name: 'upgrade_from', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Building $upgradeFromBuilding;

    public function __construct()
    {
        $this->upgradeCosts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUpgradeFromBuildingId(): int
    {
        return $this->upgrade_from;
    }

    public function setUpgradeFromBuildingId(int $upgradeFromBuildingId): BuildingUpgrade
    {
        $this->upgrade_from = $upgradeFromBuildingId;

        return $this;
    }

    public function getUpgradeToBuildingId(): int
    {
        return $this->upgrade_to;
    }

    public function setUpgradeToBuildingId(int $upgradeToBuildingId): BuildingUpgrade
    {
        $this->upgrade_to = $upgradeToBuildingId;

        return $this;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): BuildingUpgrade
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): BuildingUpgrade
    {
        $this->description = $description;

        return $this;
    }

    public function getEnergyCost(): int
    {
        return $this->getBuilding()->getEpsCost();
    }

    public function setEnergyCost(int $energyCost): BuildingUpgrade
    {
        $this->energy_cost = $energyCost;

        return $this;
    }

    public function getBuilding(): Building
    {
        return $this->upgradeToBuilding;
    }

    /**
     * @return Collection<int, BuildingCost>
     */
    public function getUpgradeCosts(): Collection
    {
        return $this->getBuilding()->getCosts();
    }
}
