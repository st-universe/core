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
use Override;
use RuntimeException;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Repository\PlanetFieldRepository;

#[Table(name: 'stu_colonies_fielddata')]
#[Index(name: 'colony_field_idx', columns: ['colonies_id', 'field_id'])]
#[Index(name: 'sandbox_field_idx', columns: ['colony_sandbox_id', 'field_id'])]
#[Index(name: 'colony_building_active_idx', columns: ['colonies_id', 'buildings_id', 'aktiv'])]
#[Index(name: 'sandbox_building_active_idx', columns: ['colony_sandbox_id', 'buildings_id', 'aktiv'])]
#[Index(name: 'active_idx', columns: ['aktiv'])]
#[Entity(repositoryClass: PlanetFieldRepository::class)]
class PlanetField implements PlanetFieldInterface
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

    #[ManyToOne(targetEntity: 'Building')]
    #[JoinColumn(name: 'buildings_id', referencedColumnName: 'id')]
    private ?BuildingInterface $building = null;

    #[ManyToOne(targetEntity: 'Terraforming')]
    #[JoinColumn(name: 'terraforming_id', referencedColumnName: 'id')]
    private ?TerraformingInterface $terraforming = null;

    #[ManyToOne(targetEntity: 'Colony')]
    #[JoinColumn(name: 'colonies_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ColonyInterface $colony = null;

    #[ManyToOne(targetEntity: 'ColonySandbox')]
    #[JoinColumn(name: 'colony_sandbox_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ColonySandboxInterface $sandbox = null;

    private bool $buildmode = false;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getFieldId(): int
    {
        return $this->field_id;
    }

    #[Override]
    public function setFieldId(int $fieldId): PlanetFieldInterface
    {
        $this->field_id = $fieldId;
        return $this;
    }

    #[Override]
    public function getFieldType(): int
    {
        return $this->type_id;
    }

    #[Override]
    public function setFieldType(int $planetFieldTypeId): PlanetFieldInterface
    {
        $this->type_id = $planetFieldTypeId;
        return $this;
    }

    #[Override]
    public function getBuildingId(): ?int
    {
        return $this->buildings_id;
    }

    #[Override]
    public function getTerraformingId(): ?int
    {
        return $this->terraforming_id;
    }

    #[Override]
    public function getIntegrity(): int
    {
        return $this->integrity;
    }

    #[Override]
    public function setIntegrity(int $integrity): PlanetFieldInterface
    {
        $this->integrity = $integrity;
        return $this;
    }

    #[Override]
    public function getActive(): int
    {
        return $this->aktiv;
    }

    #[Override]
    public function setActive(int $aktiv): PlanetFieldInterface
    {
        $this->aktiv = $aktiv;
        return $this;
    }

    #[Override]
    public function getActivateAfterBuild(): bool
    {
        return $this->activate_after_build;
    }

    #[Override]
    public function setActivateAfterBuild(bool $activateAfterBuild): PlanetFieldInterface
    {
        $this->activate_after_build = $activateAfterBuild;
        return $this;
    }

    #[Override]
    public function setBuildMode(bool $value): void
    {
        $this->buildmode = $value;
    }

    #[Override]
    public function getBuildtime(): int
    {
        return $this->getActive();
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->getActive() === 1;
    }

    #[Override]
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

    #[Override]
    public function hasHighDamage(): bool
    {
        if (!$this->isDamaged()) {
            return false;
        }
        return round((100 / $this->getBuilding()->getIntegrity()) * $this->getIntegrity()) < 50;
    }

    #[Override]
    public function isUnderConstruction(): bool
    {
        return $this->getActive() > 1;
    }

    #[Override]
    public function hasBuilding(): bool
    {
        return $this->getBuilding() !== null;
    }

    #[Override]
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

    #[Override]
    public function getBuildingState(): string
    {
        if ($this->isUnderConstruction()) {
            return 'b';
        }
        return 'a';
    }

    #[Override]
    public function getBuilding(): ?BuildingInterface
    {
        return $this->building;
    }

    #[Override]
    public function setBuilding(?BuildingInterface $building): PlanetFieldInterface
    {
        $this->building = $building;

        return $this;
    }

    #[Override]
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

    #[Override]
    public function clearBuilding(): void
    {
        $this->setBuilding(null);
        $this->setIntegrity(0);
        $this->setActive(0);
    }

    #[Override]
    public function getHost(): ColonyInterface|ColonySandboxInterface
    {
        $colony = $this->colony;
        $sandbox = $this->sandbox;

        if ($colony === null && $sandbox === null) {
            throw new RuntimeException('this should not happen');
        }

        return $colony ?? $sandbox;
    }

    #[Override]
    public function setColony(ColonyInterface $colony): PlanetFieldInterface
    {
        $this->colony = $colony;
        return $this;
    }

    #[Override]
    public function setColonySandbox(ColonySandboxInterface $sandbox): PlanetFieldInterface
    {
        $this->sandbox = $sandbox;
        return $this;
    }

    #[Override]
    public function getTerraforming(): ?TerraformingInterface
    {
        return $this->terraforming;
    }

    #[Override]
    public function setTerraforming(?TerraformingInterface $terraforming): PlanetFieldInterface
    {
        $this->terraforming = $terraforming;

        return $this;
    }

    #[Override]
    public function getDayNightPrefix(): string
    {
        $twilightZone = $this->getHost()->getTwilightZone();

        if ($twilightZone >= 0) {
            return $this->getFieldId() % $this->getHost()->getSurfaceWidth() >= $twilightZone ? 'n' : 't';
        }

        return $this->getFieldId() % $this->getHost()->getSurfaceWidth() < -$twilightZone ? 'n' : 't';
    }

    #[Override]
    public function getBuildProgress(): int
    {
        $start = $this->getBuildtime() - $this->getBuilding()->getBuildTime();
        return time() - $start;
    }

    #[Override]
    public function getOverlayWidth(): int
    {
        $buildtime = $this->getBuilding()->getBuildtime();
        $perc = max(0, @round((100 / $buildtime) * min($this->getBuildProgress(), $buildtime)));
        return (int) round((40 / 100) * $perc);
    }

    #[Override]
    public function getPictureType(): string
    {
        return $this->getBuildingId() . "/" . $this->getBuilding()->getBuildingType() . $this->getBuildingState();
    }

    #[Override]
    public function isColonizeAble(): bool
    {
        return in_array($this->getFieldType(), $this->getHost()->getColonyClass()->getColonizeableFields());
    }

    /**
     * @todo temporary, remove it.
     */
    #[Override]
    public function getConstructionStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
            ->setLabel(_('Fortschritt'))
            ->setMaxValue($this->getBuilding()->getBuildtime())
            ->setValue($this->getBuildProgress())
            ->render();
    }
}
