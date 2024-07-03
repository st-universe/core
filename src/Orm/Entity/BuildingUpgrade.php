<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\BuildingUpgradeRepository;
use Override;
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

#[Table(name: 'stu_buildings_upgrades')]
#[Index(name: 'upgrade_from_research_idx', columns: ['upgrade_from', 'research_id'])]
#[Entity(repositoryClass: BuildingUpgradeRepository::class)]
class BuildingUpgrade implements BuildingUpgradeInterface
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
     * @var ArrayCollection<int, BuildingUpgradeCostInterface>
     */
    #[OneToMany(targetEntity: 'BuildingUpgradeCost', mappedBy: 'upgrade')]
    private Collection $upgradeCosts;

    #[ManyToOne(targetEntity: 'Building')]
    #[JoinColumn(name: 'upgrade_to', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private BuildingInterface $upgradeToBuilding;

    /**
     * @var BuildingInterface
     */
    #[ManyToOne(targetEntity: 'Building')]
    #[JoinColumn(name: 'upgrade_from', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $upgradeFromBuilding;

    public function __construct()
    {
        $this->upgradeCosts = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUpgradeFromBuildingId(): int
    {
        return $this->upgrade_from;
    }

    #[Override]
    public function setUpgradeFromBuildingId(int $upgradeFromBuildingId): BuildingUpgradeInterface
    {
        $this->upgrade_from = $upgradeFromBuildingId;

        return $this;
    }

    #[Override]
    public function getUpgradeToBuildingId(): int
    {
        return $this->upgrade_to;
    }

    #[Override]
    public function setUpgradeToBuildingId(int $upgradeToBuildingId): BuildingUpgradeInterface
    {
        $this->upgrade_to = $upgradeToBuildingId;

        return $this;
    }

    #[Override]
    public function getResearchId(): int
    {
        return $this->research_id;
    }

    #[Override]
    public function setResearchId(int $researchId): BuildingUpgradeInterface
    {
        $this->research_id = $researchId;

        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): BuildingUpgradeInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function getEnergyCost(): int
    {
        return $this->getBuilding()->getEpsCost();
    }

    #[Override]
    public function setEnergyCost(int $energyCost): BuildingUpgradeInterface
    {
        $this->energy_cost = $energyCost;

        return $this;
    }

    #[Override]
    public function getBuilding(): BuildingInterface
    {
        return $this->upgradeToBuilding;
    }

    #[Override]
    public function getUpgradeCosts(): Collection
    {
        return $this->getBuilding()->getCosts();
    }
}
