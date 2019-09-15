<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Colony;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PlanetFieldRepository")
 * @Table(
 *     name="stu_colonies_fielddata",
 *     indexes={
 *         @Index(name="colony_field_idx", columns={"colonies_id","field_id"}),
 *         @Index(name="colony_building_active_idx", columns={"colonies_id", "buildings_id", "aktiv"}),
 *         @Index(name="active_idx", columns={"aktiv"})
 *     }
 * )
 **/
class PlanetField implements PlanetFieldInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") */
    private $colonies_id = 0;

    /** @Column(type="smallint") */
    private $field_id = 0;

    /** @Column(type="integer") */
    private $type = 0;

    /** @Column(type="integer", nullable=true) */
    private $buildings_id;

    /** @Column(type="integer", nullable=true) */
    private $terraforming_id;

    /** @Column(type="smallint") */
    private $integrity = 0;

    /** @Column(type="integer") */
    private $aktiv = 0;

    /**
     * @ManyToOne(targetEntity="Building")
     * @JoinColumn(name="buildings_id", referencedColumnName="id")
     */
    private $building;

    /**
     * @ManyToOne(targetEntity="Terraforming")
     * @JoinColumn(name="terraforming_id", referencedColumnName="id")
     */
    private $terraforming;

    private $buildmode = false;

    private $colony;

    private $terraformingState;

    private $terraformingopts;

    private $upgrades;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyId(): int
    {
        return $this->colonies_id;
    }

    public function setColonyId(int $colonyId): PlanetFieldInterface
    {
        $this->colonies_id = $colonyId;
        return $this;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function setFieldId(int $fieldId): PlanetFieldInterface
    {
        $this->field_id = $fieldId;
        return $this;
    }

    public function getFieldType(): int
    {
        return $this->type;
    }

    public function setFieldType(int $planetFieldTypeId): PlanetFieldInterface
    {
        $this->type = $planetFieldTypeId;
        return $this;
    }

    public function getBuildingId(): ?int
    {
        return $this->buildings_id;
    }

    public function getTerraformingId(): ?int
    {
        return $this->terraforming_id;
    }

    public function getIntegrity(): int
    {
        return $this->integrity;
    }

    public function setIntegrity(int $integrity): PlanetFieldInterface
    {
        $this->integrity = $integrity;
        return $this;
    }

    public function getActive(): int
    {
        return $this->aktiv;
    }

    public function setActive(int $aktiv): PlanetFieldInterface
    {
        $this->aktiv = $aktiv;
        return $this;
    }

    public function setBuildMode(bool $value): void
    {
        $this->buildmode = $value;
    }

    public function getFieldTypeName(): string
    {
        return getFieldName($this->getFieldType());
    }

    public function getBuildtime(): int
    {
        return $this->getActive();
    }

    public function isActive(): bool
    {
        return $this->getActive() === 1;
    }

    public function isActivateable(): bool
    {
        if ($this->isInConstruction()) {
            return false;
        }
        return $this->getBuilding()->isActivateable();
    }

    public function hasHighDamage(): bool
    {
        if (!$this->isDamaged()) {
            return false;
        }
        if (round((100 / $this->getBuilding()->getIntegrity()) * $this->getIntegrity()) < 50) {
            return true;
        }
        return false;
    }

    public function isInConstruction(): bool
    {
        return $this->getActive() > 1;
    }

    public function hasBuilding(): bool
    {
        return $this->getBuildingId() !== null;
    }

    public function getCssClass(): string
    {
        if ($this->buildmode === true) {
            return 'cfb';
        }
        if ($this->isActive()) {
            return 'cfa';
        }
        return 'cfd';
    }

    public function getBuildingState(): string
    {
        if ($this->isInConstruction()) {
            return 'b';
        }
        return 'a';
    }

    public function getBuilding(): BuildingInterface
    {
        return $this->building;
    }

    public function setBuilding(?BuildingInterface $building): PlanetFieldInterface
    {
        $this->building = $building;

        return $this;
    }

    public function isDamaged(): bool
    {
        if (!$this->hasBuilding()) {
            return false;
        }
        if ($this->isInConstruction()) {
            return false;
        }
        return $this->getIntegrity() != $this->getBuilding()->getIntegrity();
    }

    public function clearBuilding(): void
    {
        // @todo refactor
        global $container;

        $buildingFunctionActionMapper = $container->get(BuildingFunctionActionMapperInterface::class);

        foreach ($this->getBuilding()->getFunctions() as $function) {
            $buildingFunctionId = $function->getFunction();

            $handler = $buildingFunctionActionMapper->map($buildingFunctionId);
            if ($handler !== null) {
                $handler->destruct((int)$this->getColonyId(), $buildingFunctionId);
            }
        }
        $this->setBuilding(null);
        $this->setIntegrity(0);
        $this->setActive(0);
    }

    public function getColony(): Colony
    {
        if ($this->colony === null) {
            $this->colony = new Colony($this->getColonyId());
        }
        return $this->colony;
    }

    public function getTerraforming(): ?TerraformingInterface
    {
        return $this->terraforming;
    }

    public function setTerraforming(?TerraformingInterface $terraforming): PlanetFieldInterface
    {
        $this->terraforming = $terraforming;

        return $this;
    }

    public function getTerraformingState(): ?ColonyTerraformingInterface
    {
        if ($this->terraformingState === null) {
            // @todo refactor
            global $container;

            $this->terraformingState = $container->get(ColonyTerraformingRepositoryInterface::class)->getByColonyAndField(
                (int)$this->getColonyId(),
                (int)$this->getId()
            );
        }
        return $this->terraformingState;
    }

    public function getTerraformingOptions(): array
    {
        if ($this->terraformingopts === null) {
            // @todo refactor
            global $container;

            $this->terraformingopts = $container->get(TerraformingRepositoryInterface::class)->getBySourceFieldType(
                (int)$this->getFieldType()
            );
        }
        return $this->terraformingopts;
    }

    public function getTitleString(): string
    {
        if (!$this->hasBuilding()) {
            if ($this->getTerraformingId() !== null) {
                return $this->getTerraforming()->getDescription() . " läuft bis " . parseDateTime($this->getTerraformingState()->getFinishDate());
            }
            return $this->getFieldTypeName();
        }
        if ($this->isinConstruction()) {
            return sprintf(
                _('In Bau: %s auf %s - Fertigstellung: %s'),
                $this->getBuilding()->getName(),
                $this->getFieldTypeName(),
                date('d.m.Y H:i', $this->getBuildtime())
            );
        }
        if (!$this->isActivateable()) {
            return $this->getBuilding()->getName() . " auf " . $this->getFieldTypeName();
        }
        if ($this->isActive()) {
            return $this->getBuilding()->getName() . " (aktiviert) auf " . $this->getFieldTypeName();
        }
        return $this->getBuilding()->getName() . " (deaktiviert) auf " . $this->getFieldTypeName();
    }

    public function getBuildProgress(): int
    {
        $start = $this->getBuildtime() - $this->getBuilding()->getBuildTime();
        return time() - $start;
    }

    public function getOverlayWidth(): int
    {
        $perc = getPercentage($this->getBuildProgress(), $this->getBuilding()->getBuildtime());
        return (int) round((40 / 100) * $perc);
    }

    public function getPictureType(): string
    {
        return $this->getBuildingId() . "/" . $this->getBuilding()->getBuildingType() . $this->getBuildingState();
    }

    public function getPossibleUpgrades(): array
    {
        if ($this->isInConstruction() || $this->getBuildingId() == 0) {
            return [];
        }
        if ($this->upgrades === null) {
            // @todo refactor
            global $container;
            $this->upgrades = $container
                ->get(BuildingUpgradeRepositoryInterface::class)
                ->getByBuilding((int)$this->getBuildingId(), (int)$this->getColony()->getUserId());
        }
        return $this->upgrades;
    }

    public function isColonizeAble(): bool
    {
        return in_array($this->getFieldType(), $this->getColony()->getPlanetType()->getColonizeableFields());
    }

    public function hasUpgradeOrTerraformingOption(): bool
    {
        return (!$this->isInConstruction() && count($this->getPossibleUpgrades()) > 0) || (count($this->getTerraformingOptions()) > 0 && !$this->hasBuilding());
    }
}
