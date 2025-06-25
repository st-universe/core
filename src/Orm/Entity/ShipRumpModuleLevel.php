<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Repository\ShipRumpModuleLevelRepository;

#[Table(name: 'stu_rump_module_level')]
#[Entity(repositoryClass: ShipRumpModuleLevelRepository::class)]
class ShipRumpModuleLevel implements ShipRumpModuleLevelInterface
{
    public const string DEFAULT_LEVEL_KEY = 'default';
    public const string MIN_LEVEL_KEY = 'min';
    public const string MAX_LEVEL_KEY = 'max';
    public const string MANDATORY_KEY = 'mandatory';

    #[Id]
    #[OneToOne(targetEntity: SpacecraftRump::class)]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRumpInterface $rump;

    /** @var array<int, array<string, bool|int|string>> */
    #[Column(type: 'json', nullable: true)]
    private ?array $type_values = null;

    #[Override]
    public function getMinimumLevel(SpacecraftModuleTypeEnum $type): int
    {
        $value = $this->getValuesForType($type)[self::MIN_LEVEL_KEY];
        if (!is_integer($value)) {
            throw new RuntimeException(sprintf('minimum level of type %s is not an integer: %s', $type->name, (string)$value));
        }

        return $value;
    }

    #[Override]
    public function getDefaultLevel(SpacecraftModuleTypeEnum $type): int
    {
        $value = $this->getValuesForType($type)[self::DEFAULT_LEVEL_KEY];
        if (!is_integer($value)) {
            throw new RuntimeException(sprintf('default level of type %s is not an integer: %s', $type->name, (string)$value));
        }

        return $value;
    }

    #[Override]
    public function getMaximumLevel(SpacecraftModuleTypeEnum $type): int
    {
        $value = $this->getValuesForType($type)[self::MAX_LEVEL_KEY];
        if (!is_integer($value)) {
            throw new RuntimeException(sprintf('maximum level of type %s is not an integer: %s', $type->name, (string)$value));
        }

        return $value;
    }

    #[Override]
    public function isMandatory(SpacecraftModuleTypeEnum $type): bool
    {
        $value = $this->getValuesForType($type)[self::MANDATORY_KEY];
        if (!is_bool($value)) {
            throw new RuntimeException(sprintf('mandatory value of type %s is not a bool: %s', $type->name, (string)$value));
        }

        return $value;
    }

    #[Override]
    public function setValue(SpacecraftModuleTypeEnum $type, string $key, $value): ShipRumpModuleLevelInterface
    {
        if ($this->type_values === null) {
            $this->type_values = [];
        }

        if (!array_key_exists($type->value, $this->type_values)) {
            $this->type_values[$type->value] = [];
        }

        $this->type_values[$type->value][$key] = $value;

        return $this;
    }

    /** @return array<string, int|bool|string> */
    private function getValuesForType(SpacecraftModuleTypeEnum $type): array
    {
        if (
            $this->type_values === null
            || !array_key_exists($type->value, $this->type_values)
        ) {
            throw new RuntimeException(sprintf('no values for module type: %s', $type->name));
        }

        return $this->type_values[$type->value];
    }

    #[Override]
    public function getMandatoryModulesCount(): ?int
    {
        return array_reduce(
            array_filter(SpacecraftModuleTypeEnum::cases(), fn(SpacecraftModuleTypeEnum $type): bool => !$type->isSpecialSystemType()),
            fn(int $value, SpacecraftModuleTypeEnum $type): int => $value + $this->isMandatory($type) !== 0 ? 1 : 0,
            0
        );
    }
}
