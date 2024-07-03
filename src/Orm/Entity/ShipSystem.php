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
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Repository\ShipSystemRepository;

#[Table(name: 'stu_ship_system')]
#[Index(name: 'ship_system_ship_idx', columns: ['ship_id'])]
#[Index(name: 'ship_system_status_idx', columns: ['status'])]
#[Index(name: 'ship_system_type_idx', columns: ['system_type'])]
#[Index(name: 'ship_system_module_idx', columns: ['module_id'])]
#[Index(name: 'ship_system_mode_idx', columns: ['mode'])]
#[Entity(repositoryClass: ShipSystemRepository::class)]
class ShipSystem implements ShipSystemInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $ship_id = 0;

    #[Column(type: 'smallint', enumType: ShipSystemTypeEnum::class)]
    private ShipSystemTypeEnum $system_type = ShipSystemTypeEnum::SYSTEM_HULL;

    #[Column(type: 'integer', nullable: true)]
    private ?int $module_id = 0;

    #[Column(type: 'smallint')]
    private int $status = 0;

    #[Column(type: 'smallint')]
    private int $mode = 1;

    #[Column(type: 'integer', nullable: true)]
    private ?int $cooldown = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $data = null;

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ModuleInterface $module = null;

    #[ManyToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return $this->system_type;
    }

    #[Override]
    public function setSystemType(ShipSystemTypeEnum $type): ShipSystemInterface
    {
        $this->system_type = $type;

        return $this;
    }

    #[Override]
    public function getModuleId(): int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): ShipSystemInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    #[Override]
    public function getStatus(): int
    {
        return $this->status;
    }

    #[Override]
    public function setStatus(int $status): ShipSystemInterface
    {
        $this->status = $status;

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->getSystemType()->getDescription();
    }

    #[Override]
    public function getCssClass(): string
    {
        if ($this->getStatus() < 1) {
            return _("sysStatus0");
        } elseif ($this->getStatus() < 26) {
            return _("sysStatus1to25");
        } elseif ($this->getStatus() < 51) {
            return _("sysStatus26to50");
        } elseif ($this->getStatus() < 76) {
            return _("sysStatus51to75");
        } else {
            return _("sysStatus76to100");
        }
    }

    #[Override]
    public function getMode(): int
    {
        return $this->mode;
    }

    #[Override]
    public function setMode(int $mode): ShipSystemInterface
    {
        $this->mode = $mode;

        return $this;
    }

    #[Override]
    public function getCooldown(): ?int
    {
        return $this->cooldown;
    }

    #[Override]
    public function setCooldown(int $cooldown): ShipSystemInterface
    {
        $this->cooldown = $cooldown;

        return $this;
    }

    #[Override]
    public function getModule(): ?ModuleInterface
    {
        return $this->module;
    }

    #[Override]
    public function setModule(ModuleInterface $module): ShipSystemInterface
    {
        $this->module = $module;

        return $this;
    }

    #[Override]
    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    #[Override]
    public function setShip(ShipInterface $ship): ShipSystemInterface
    {
        $this->ship = $ship;
        return $this;
    }

    #[Override]
    public function getData(): ?string
    {
        return $this->data;
    }

    #[Override]
    public function setData(string $data): ShipSystemInterface
    {
        $this->data = $data;
        return $this;
    }

    #[Override]
    public function determineSystemLevel(): int
    {
        $module = $this->getModule();

        if ($module !== null && $module->getLevel() > 0) {
            return $module->getLevel();
        } else {
            return max(1, $this->getShip()->getRump()->getModuleLevel() - 1);
        }
    }
}
