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
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Repository\SpacecraftSystemRepository;

#[Table(name: 'stu_spacecraft_system')]
#[Index(name: 'spacecraft_system_status_idx', columns: ['status'])]
#[Index(name: 'spacecraft_system_type_idx', columns: ['system_type'])]
#[Index(name: 'spacecraft_system_mode_idx', columns: ['mode'])]
#[Entity(repositoryClass: SpacecraftSystemRepository::class)]
class SpacecraftSystem
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $spacecraft_id = 0;

    #[Column(type: 'smallint', enumType: SpacecraftSystemTypeEnum::class)]
    private SpacecraftSystemTypeEnum $system_type = SpacecraftSystemTypeEnum::HULL;

    #[Column(type: 'integer', nullable: true)]
    private ?int $module_id = 0;

    #[Column(type: 'smallint')]
    private int $status = 0;

    #[Column(type: 'smallint', enumType: SpacecraftSystemModeEnum::class)]
    private SpacecraftSystemModeEnum $mode = SpacecraftSystemModeEnum::MODE_OFF;

    #[Column(type: 'integer', nullable: true)]
    private ?int $cooldown = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $data = null;

    #[ManyToOne(targetEntity: Module::class)]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Module $module = null;

    #[ManyToOne(targetEntity: Spacecraft::class, inversedBy: 'systems')]
    #[JoinColumn(name: 'spacecraft_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Spacecraft $spacecraft;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return $this->system_type;
    }

    public function setSystemType(SpacecraftSystemTypeEnum $type): SpacecraftSystem
    {
        $this->system_type = $type;

        return $this;
    }

    public function getModuleId(): ?int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): SpacecraftSystem
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): SpacecraftSystem
    {
        $this->status = $status;

        return $this;
    }

    public function isHealthy(): bool
    {
        return $this->getStatus() > 0;
    }

    public function getName(): string
    {
        return $this->getSystemType()->getDescription();
    }

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

    public function getMode(): SpacecraftSystemModeEnum
    {
        return $this->mode;
    }

    public function setMode(SpacecraftSystemModeEnum $mode): SpacecraftSystem
    {
        $this->mode = $mode;

        return $this;
    }

    public function getCooldown(): ?int
    {
        return $this->cooldown > time() ? $this->cooldown : null;
    }

    public function setCooldown(int $cooldown): SpacecraftSystem
    {
        $this->cooldown = $cooldown;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(Module $module): SpacecraftSystem
    {
        $this->module = $module;

        return $this;
    }

    public function getSpacecraft(): Spacecraft
    {
        return $this->spacecraft;
    }

    public function setSpacecraft(Spacecraft $spacecraft): SpacecraftSystem
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): SpacecraftSystem
    {
        $this->data = $data;
        return $this;
    }

    public function determineSystemLevel(): int
    {
        $module = $this->getModule();

        if ($module !== null && $module->getLevel() > 0) {
            return $module->getLevel();
        } else {
            return max(1, $this->getSpacecraft()->getRump()->getBaseValues()->getModuleLevel() - 1);
        }
    }

    public function hasSpecial(ModuleSpecialAbilityEnum $ability): bool
    {
        return $this->module !== null && $this->module->hasSpecial($ability);
    }

    public function __toString(): string
    {
        return sprintf(
            '"%s": %s',
            $this->system_type->name,
            $this->data ?? 'null'
        );
    }
}
