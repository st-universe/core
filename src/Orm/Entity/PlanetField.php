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
use RuntimeException;
use Stu\Orm\Repository\PlanetFieldRepository;

#[Table(name: 'stu_colonies_fielddata')]
#[Index(name: 'colony_field_idx', columns: ['colonies_id', 'field_id'])]
#[Index(name: 'sandbox_field_idx', columns: ['colony_sandbox_id', 'field_id'])]
#[Index(name: 'colony_building_active_idx', columns: ['colonies_id', 'buildings_id', 'aktiv'])]
#[Index(name: 'sandbox_building_active_idx', columns: ['colony_sandbox_id', 'buildings_id', 'aktiv'])]
#[Index(name: 'active_idx', columns: ['aktiv'])]
#[Entity(repositoryClass: PlanetFieldRepository::class)]
class PlanetField
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $colonies_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $colony_sandbox_id = null;

    #[Column(type: 'smallint')]
    private int $field_id = 0;

    #[Column(type: 'integer')]
    private int $type_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $buildings_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $terraforming_id = null;

    #[Column(type: 'smallint')]
    private int $integrity = 0;

    #[Column(type: 'integer')]
    private int $aktiv = 0;

    #[Column(type: 'boolean')]
    private bool $activate_after_build = true;

    #[ManyToOne(targetEntity: Building::class)]
    #[JoinColumn(name: 'buildings_id', referencedColumnName: 'id')]
    private ?Building $building = null;

    #[ManyToOne(targetEntity: Terraforming::class)]
    #[JoinColumn(name: 'terraforming_id', referencedColumnName: 'id')]
    private ?Terraforming $terraforming = null;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colonies_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Colony $colony = null;

    #[ManyToOne(targetEntity: ColonySandbox::class)]
    #[JoinColumn(name: 'colony_sandbox_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ColonySandbox $sandbox = null;

    private bool $buildmode = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function setFieldId(int $fieldId): PlanetField
    {
        $this->field_id = $fieldId;
        return $this;
    }

    public function getFieldType(): int
    {
        return $this->type_id;
    }

    public function setFieldType(int $planetFieldTypeId): PlanetField
    {
        $this->type_id = $planetFieldTypeId;
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

    public function setIntegrity(int $integrity): PlanetField
    {
        $this->integrity = $integrity;
        return $this;
    }

    public function getActive(): int
    {
        return $this->aktiv;
    }

    public function setActive(int $aktiv): PlanetField
    {
        $this->aktiv = $aktiv;
        return $this;
    }

    public function getActivateAfterBuild(): bool
    {
        return $this->activate_after_build;
    }

    public function setActivateAfterBuild(bool $activateAfterBuild): PlanetField
    {
        $this->activate_after_build = $activateAfterBuild;
        return $this;
    }

    public function setBuildMode(bool $value): void
    {
        $this->buildmode = $value;
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
        if ($this->isUnderConstruction()) {
            return false;
        }
        return $this->getBuilding()->isActivateable();
    }

    public function hasHighDamage(): bool
    {
        if (!$this->isDamaged()) {
            return false;
        }
        return round((100 / $this->getBuilding()->getIntegrity()) * $this->getIntegrity()) < 50;
    }

    public function isUnderConstruction(): bool
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
        if ($this->hasBuilding()) {
            if ($this->isUnderConstruction()) {
                return 'cfc';
            }

            if ($this->isActivateable()) {
                return 'cfd';
            }
        }

        return 'cfu';
    }

    public function getBuildingState(): string
    {
        if ($this->isUnderConstruction()) {
            return 'b';
        }
        return 'a';
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(?Building $building): PlanetField
    {
        $this->building = $building;

        return $this;
    }

    public function isDamaged(): bool
    {
        if (!$this->hasBuilding()) {
            return false;
        }
        if ($this->isUnderConstruction()) {
            return false;
        }
        return $this->getIntegrity() !== $this->getBuilding()->getIntegrity();
    }

    public function clearBuilding(): void
    {
        $this->setBuilding(null);
        $this->setIntegrity(0);
        $this->setActive(0);
    }

    public function getHost(): Colony|ColonySandbox
    {
        $colony = $this->colony;
        $sandbox = $this->sandbox;

        if ($colony === null && $sandbox === null) {
            throw new RuntimeException('Both colony and sandbox are null. Ensure one is set before calling getHost().');
        }

        return $colony ?? $sandbox;
    }

    public function setColony(Colony $colony): PlanetField
    {
        $this->colony = $colony;
        return $this;
    }

    public function setColonySandbox(ColonySandbox $sandbox): PlanetField
    {
        $this->sandbox = $sandbox;
        return $this;
    }

    public function getTerraforming(): ?Terraforming
    {
        return $this->terraforming;
    }

    public function setTerraforming(?Terraforming $terraforming): PlanetField
    {
        $this->terraforming = $terraforming;

        return $this;
    }

    public function getDayNightPrefix(int $timestamp): string
    {
        $twilightZone = $this->getHost()->getTwilightZone($timestamp);

        if ($twilightZone >= 0) {
            return $this->getFieldId() % $this->getHost()->getSurfaceWidth() >= $twilightZone ? 'n' : 't';
        }

        return $this->getFieldId() % $this->getHost()->getSurfaceWidth() < -$twilightZone ? 'n' : 't';
    }

    public function getBuildProgress(): int
    {
        $building = $this->getBuilding();
        if ($building === null) {
            return 0;
        }

        $start = $this->getBuildtime() - $building->getBuildTime();
        return time() - $start;
    }

    public function getOverlayWidth(): int
    {
        $building = $this->getBuilding();
        if ($building === null) {
            throw new RuntimeException('building is null');
        }

        $buildtime = $building->getBuildtime();
        $perc = max(0, @round((100 / $buildtime) * min($this->getBuildProgress(), $buildtime)));
        return (int) round((40 / 100) * $perc);
    }

    public function getPictureType(): string
    {
        return $this->getBuildingId() . "/" . $this->getBuilding()->getBuildingType() . $this->getBuildingState();
    }

    public function isColonizeAble(): bool
    {
        return in_array($this->getFieldType(), $this->getHost()->getColonyClass()->getColonizeableFields());
    }
}
