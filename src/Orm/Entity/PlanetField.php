<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
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
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
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

    /** @Column(type="boolean") */
    private $activate_after_build = true;

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

    /**
     * @ManyToOne(targetEntity="Colony")
     * @JoinColumn(name="colonies_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colony;

    private $buildmode = false;

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

    public function getActivateAfterBuild(): bool
    {
        return $this->activate_after_build;
    }

    public function setActivateAfterBuild(bool $activateAfterBuild): PlanetFieldInterface
    {
        $this->activate_after_build = $activateAfterBuild;
        return $this;
    }

    public function setBuildMode(bool $value): void
    {
        $this->buildmode = $value;
    }

    public function getFieldTypeName(): string
    {
        // @todo remove
        global $container;

        return $container->get(PlanetFieldTypeRetrieverInterface::class)->getDescription($this->getFieldType());
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
        if ($this->hasBuilding() === false) {
            return false;
        }
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
        return $this->getBuilding() !== null;
    }

    public function getCssClass(): string
    {
        if ($this->buildmode === true) {
            return 'cfb';
        }
        if ($this->isActive()) {
            if ($this->isDamaged()) {
                return 'cfld';
            }
            return 'cfa';
        }
        if ($this->hasHighDamage()) {
            return 'cfhd';
        }
        if ($this->hasBuilding() && $this->isActivateable()) {
            return 'cfd';
        }

        return 'cfu';
    }

    public function getBuildingState(): string
    {
        if ($this->isInConstruction()) {
            return 'b';
        }
        return 'a';
    }

    public function getBuilding(): ?BuildingInterface
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
                $handler->destruct((int) $this->getColonyId(), $buildingFunctionId);
            }
        }
        $this->setBuilding(null);
        $this->setIntegrity(0);
        $this->setActive(0);
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): PlanetFieldInterface
    {
        $this->colony = $colony;
        return $this;
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

    public function getNightPrefix(): string
    {
        $twilightZone = $this->getColony()->getTwilightZone();

        if ($twilightZone >= 0) {
            return $this->getFieldId() % $this->getColony()->getSurfaceWidth() >= $twilightZone ? 'n' : '';
        }

        return $this->getFieldId() % $this->getColony()->getSurfaceWidth() < $twilightZone ? 'n' : '';
    }

    public function getTerraformingState(): ?ColonyTerraformingInterface
    {
        if ($this->terraformingState === null) {
            // @todo refactor
            global $container;

            $this->terraformingState = $container->get(ColonyTerraformingRepositoryInterface::class)->getByColonyAndField(
                (int) $this->getColonyId(),
                (int) $this->getId()
            );
        }
        return $this->terraformingState;
    }

    public function getTerraformingOptions(): array
    {
        if ($this->terraformingopts === null) {
            // @todo refactor
            global $container;

            $userId = $container->get(GameControllerInterface::class)->getUser()->getId();

            $this->terraformingopts = $container->get(TerraformingRepositoryInterface::class)->getBySourceFieldType(
                (int) $this->getFieldType(),
                (int) $userId
            );
        }
        return $this->terraformingopts;
    }

    public function getTitleString(): string
    {
        if (!$this->hasBuilding()) {
            if ($this->getTerraformingId() !== null) {
                return sprintf(
                    "%s läuft bis %s",
                    $this->getTerraforming()->getDescription(),
                    date('d.m.Y H:i', $this->getTerraformingState()->getFinishDate())
                );
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
            if ($this->isDamaged()) {
                return $this->getBuilding()->getName() . " (aktiviert, beschädigt) auf " . $this->getFieldTypeName();
            }
            return $this->getBuilding()->getName() . " (aktiviert) auf " . $this->getFieldTypeName();
        }
        if ($this->hasHighDamage()) {
            return $this->getBuilding()->getName() . " (stark beschädigt) auf " . $this->getFieldTypeName();
        }
        if ($this->isActivateable()) {
            return $this->getBuilding()->getName() . " (deaktiviert) auf " . $this->getFieldTypeName();
        }
        return $this->getBuilding()->getName() . " auf " . $this->getFieldTypeName();
    }

    public function getBuildProgress(): int
    {
        $start = $this->getBuildtime() - $this->getBuilding()->getBuildTime();
        return time() - $start;
    }

    public function getOverlayWidth(): int
    {
        $buildtime = $this->getBuilding()->getBuildtime();
        $perc = max(0, @round((100 / $buildtime) * min($this->getBuildProgress(), $buildtime)));
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
                ->getByBuilding((int) $this->getBuildingId(), (int) $this->getColony()->getUserId());
        }
        return $this->upgrades;
    }

    public function isColonizeAble(): bool
    {
        return in_array($this->getFieldType(), $this->getColony()->getPlanetType()->getColonizeableFields());
    }

    public function isUnderground(): bool
    {
        return $this->field_id >= 80;
    }

    public function hasUpgradeOrTerraformingOption(): bool
    {
        return (!$this->isInConstruction() && count($this->getPossibleUpgrades()) > 0) || (count($this->getTerraformingOptions()) > 0 && !$this->hasBuilding());
    }

    /**
     * @todo temporary, remove it.
     */
    public function getConstructionStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
            ->setLabel(_('Fortschritt'))
            ->setMaxValue($this->getBuilding()->getBuildtime())
            ->setValue($this->getBuildProgress())
            ->render();
    }

    /**
     * @todo temporary, remove it.
     */
    public function getTerraformingStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
            ->setLabel(_('Fortschritt'))
            ->setMaxValue($this->getTerraforming()->getDuration())
            ->setValue($this->getTerraformingState()->getProgress())
            ->render();
    }
}
