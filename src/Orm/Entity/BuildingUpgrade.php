<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingUpgradeRepository")
 * @Table(
 *     name="stu_buildings_upgrades",
 *     indexes={
 *          @Index(name="upgrade_from_research_idx", columns={"upgrade_from", "research_id"})
 *     }
 * )
 **/
class BuildingUpgrade implements BuildingUpgradeInterface
{
    /**
     * @Id
     * @Column(type="bigint")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $upgrade_from = 0;

    /** @Column(type="integer") * */
    private $upgrade_to = 0;

    /** @Column(type="integer") * */
    private $research_id = 0;

    /** @Column(type="string") * */
    private $description = '';

    /** @Column(type="integer") * */
    private $energy_cost = 0;

    /**
     * @OneToMany(targetEntity="BuildingUpgradeCost", mappedBy="upgrade")
     */
    private $upgradeCosts;

    /**
     * @ManyToOne(targetEntity="Building")
     * @JoinColumn(name="upgrade_to", referencedColumnName="id", onDelete="CASCADE")
     */
    private $upgradeToBuilding;

    /**
     * @ManyToOne(targetEntity="Building")
     * @JoinColumn(name="upgrade_from", referencedColumnName="id", onDelete="CASCADE")
     */
    private $upgradeFromBuilding;

    public function __construct()
    {
        $this->upgradeCosts = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUpgradeFromBuildingId(): int
    {
        return $this->upgrade_from;
    }

    public function setUpgradeFromBuildingId(int $upgradeFromBuildingId): BuildingUpgradeInterface
    {
        $this->upgrade_from = $upgradeFromBuildingId;

        return $this;
    }

    public function getUpgradeToBuildingId(): int
    {
        return $this->upgrade_to;
    }

    public function setUpgradeToBuildingId(int $upgradeToBuildingId): BuildingUpgradeInterface
    {
        $this->upgrade_to = $upgradeToBuildingId;

        return $this;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): BuildingUpgradeInterface
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): BuildingUpgradeInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getEnergyCost(): int
    {
        return $this->getBuilding()->getEpsCost();
    }

    public function setEnergyCost(int $energyCost): BuildingUpgradeInterface
    {
        $this->energy_cost = $energyCost;

        return $this;
    }

    public function getBuilding(): BuildingInterface
    {
        return $this->upgradeToBuilding;
    }

    public function getUpgradeCosts(): Collection
    {
        return $this->getBuilding()->getCosts();
    }
}
