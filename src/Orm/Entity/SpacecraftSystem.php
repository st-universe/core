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
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Repository\SpacecraftSystemRepository;

#[Table(name: 'stu_spacecraft_system')]
#[Index(name: 'spacecraft_system_status_idx', columns: ['status'])]
#[Index(name: 'spacecraft_system_type_idx', columns: ['system_type'])]
#[Index(name: 'spacecraft_system_mode_idx', columns: ['mode'])]
#[Entity(repositoryClass: SpacecraftSystemRepository::class)]
class SpacecraftSystem implements SpacecraftSystemInterface
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

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ModuleInterface $module = null;

    #[ManyToOne(targetEntity: 'Spacecraft')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftInterface $spacecraft;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return $this->system_type;
    }

    #[Override]
    public function setSystemType(SpacecraftSystemTypeEnum $type): SpacecraftSystemInterface
    {
        $this->system_type = $type;

        return $this;
    }

    #[Override]
    public function getModuleId(): ?int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): SpacecraftSystemInterface
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
    public function setStatus(int $status): SpacecraftSystemInterface
    {
        $this->status = $status;

        return $this;
    }

    #[Override]
    public function isHealthy(): bool
    {
        return $this->getStatus() > 0;
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
    public function getMode(): SpacecraftSystemModeEnum
    {
        return $this->mode;
    }

    #[Override]
    public function setMode(SpacecraftSystemModeEnum $mode): SpacecraftSystemInterface
    {
        $this->mode = $mode;

        return $this;
    }

    #[Override]
    public function getCooldown(): ?int
    {
        return $this->cooldown > time() ? $this->cooldown : null;
    }

    #[Override]
    public function setCooldown(int $cooldown): SpacecraftSystemInterface
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
    public function setModule(ModuleInterface $module): SpacecraftSystemInterface
    {
        $this->module = $module;

        return $this;
    }

    #[Override]
    public function getSpacecraft(): SpacecraftInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function setSpacecraft(SpacecraftInterface $spacecraft): SpacecraftSystemInterface
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    #[Override]
    public function getData(): ?string
    {
        return $this->data;
    }

    #[Override]
    public function setData(string $data): SpacecraftSystemInterface
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
            return max(1, $this->getSpacecraft()->getRump()->getModuleLevel() - 1);
        }
    }

    #[Override]
    public function hasSpecial(ModuleSpecialAbilityEnum $ability): bool
    {
        return $this->module !== null && $this->module->hasSpecial($ability);
    }

    #[Override]
    public function __toString(): string
    {
        return sprintf(
            '"%s": %s',
            $this->system_type->name,
            $this->data ?? 'null'
        );
    }
}
