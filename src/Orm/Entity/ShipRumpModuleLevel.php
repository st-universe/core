<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Repository\ShipRumpModuleLevelRepository;

#[Table(name: 'stu_rumps_module_level')]
#[Index(name: 'rump_module_level_ship_rump_idx', columns: ['rump_id'])]
#[Entity(repositoryClass: ShipRumpModuleLevelRepository::class)]
class ShipRumpModuleLevel implements ShipRumpModuleLevelInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'smallint')]
    private int $module_level_1 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_1 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_1_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_1_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_2 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_2 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_2_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_2_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_3 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_3 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_3_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_3_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_4 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_4 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_4_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_4_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_5 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_5 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_5_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_5_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_6 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_6 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_6_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_6_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_7 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_7 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_7_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_7_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_8 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_8 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_8_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_8_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_10 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_10 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_10_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_10_max = 0;

    #[Column(type: 'smallint')]
    private int $module_level_11 = 0;

    #[Column(type: 'smallint')]
    private int $module_mandatory_11 = 0;

    #[Column(type: 'smallint')]
    private int $module_level_11_min = 0;

    #[Column(type: 'smallint')]
    private int $module_level_11_max = 0;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    #[Override]
    public function getModuleLevel1(): int
    {
        return $this->module_level_1;
    }

    #[Override]
    public function setModuleLevel1(int $moduleLevel1): ShipRumpModuleLevelInterface
    {
        $this->module_level_1 = $moduleLevel1;

        return $this;
    }

    #[Override]
    public function getModuleMandatory1(): bool
    {
        return $this->module_mandatory_1 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory1(int $moduleMandatory1): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_1 = $moduleMandatory1;

        return $this;
    }

    #[Override]
    public function getModuleLevel1Min(): int
    {
        return $this->module_level_1_min;
    }

    #[Override]
    public function setModuleLevel1Min(int $moduleLevel1Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_1_min = $moduleLevel1Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel1Max(): int
    {
        return $this->module_level_1_max;
    }

    #[Override]
    public function setModuleLevel1Max(int $moduleLevel1Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_1_max = $moduleLevel1Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel2(): int
    {
        return $this->module_level_2;
    }

    #[Override]
    public function setModuleLevel2(int $moduleLevel2): ShipRumpModuleLevelInterface
    {
        $this->module_level_2 = $moduleLevel2;

        return $this;
    }

    #[Override]
    public function getModuleMandatory2(): bool
    {
        return $this->module_mandatory_2 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory2(int $moduleMandatory2): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_2 = $moduleMandatory2;

        return $this;
    }

    #[Override]
    public function getModuleLevel2Min(): int
    {
        return $this->module_level_2_min;
    }

    #[Override]
    public function setModuleLevel2Min(int $moduleLevel2Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_2_min = $moduleLevel2Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel2Max(): int
    {
        return $this->module_level_2_max;
    }

    #[Override]
    public function setModuleLevel2Max(int $moduleLevel2Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_2_max = $moduleLevel2Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel3(): int
    {
        return $this->module_level_3;
    }

    #[Override]
    public function setModuleLevel3(int $moduleLevel3): ShipRumpModuleLevelInterface
    {
        $this->module_level_3 = $moduleLevel3;

        return $this;
    }

    #[Override]
    public function getModuleMandatory3(): bool
    {
        return $this->module_mandatory_3 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory3(int $moduleMandatory3): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_3 = $moduleMandatory3;

        return $this;
    }

    #[Override]
    public function getModuleLevel3Min(): int
    {
        return $this->module_level_3_min;
    }

    #[Override]
    public function setModuleLevel3Min(int $moduleLevel3Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_3_min = $moduleLevel3Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel3Max(): int
    {
        return $this->module_level_3_max;
    }

    #[Override]
    public function setModuleLevel3Max(int $moduleLevel3Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_3_max = $moduleLevel3Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel4(): int
    {
        return $this->module_level_4;
    }

    #[Override]
    public function setModuleLevel4(int $moduleLevel4): ShipRumpModuleLevelInterface
    {
        $this->module_level_4 = $moduleLevel4;

        return $this;
    }

    #[Override]
    public function getModuleMandatory4(): bool
    {
        return $this->module_mandatory_4 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory4(int $moduleMandatory4): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_4 = $moduleMandatory4;

        return $this;
    }

    #[Override]
    public function getModuleLevel4Min(): int
    {
        return $this->module_level_4_min;
    }

    #[Override]
    public function setModuleLevel4Min(int $moduleLevel4Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_4_min = $moduleLevel4Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel4Max(): int
    {
        return $this->module_level_4_max;
    }

    #[Override]
    public function setModuleLevel4Max(int $moduleLevel4Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_4_max = $moduleLevel4Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel5(): int
    {
        return $this->module_level_5;
    }

    #[Override]
    public function setModuleLevel5(int $moduleLevel5): ShipRumpModuleLevelInterface
    {
        $this->module_level_5 = $moduleLevel5;

        return $this;
    }

    #[Override]
    public function getModuleMandatory5(): bool
    {
        return $this->module_mandatory_5 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory5(int $moduleMandatory5): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_5 = $moduleMandatory5;

        return $this;
    }

    #[Override]
    public function getModuleLevel5Min(): int
    {
        return $this->module_level_5_min;
    }

    #[Override]
    public function setModuleLevel5Min(int $moduleLevel5Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_5_min = $moduleLevel5Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel5Max(): int
    {
        return $this->module_level_5_max;
    }

    #[Override]
    public function setModuleLevel5Max(int $moduleLevel5Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_5_max = $moduleLevel5Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel6(): int
    {
        return $this->module_level_6;
    }

    #[Override]
    public function setModuleLevel6(int $moduleLevel6): ShipRumpModuleLevelInterface
    {
        $this->module_level_6 = $moduleLevel6;

        return $this;
    }

    #[Override]
    public function getModuleMandatory6(): bool
    {
        return $this->module_mandatory_6 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory6(int $moduleMandatory6): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_6 = $moduleMandatory6;

        return $this;
    }

    #[Override]
    public function getModuleLevel6Min(): int
    {
        return $this->module_level_6_min;
    }

    #[Override]
    public function setModuleLevel6Min(int $moduleLevel6Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_6_min = $moduleLevel6Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel6Max(): int
    {
        return $this->module_level_6_max;
    }

    #[Override]
    public function setModuleLevel6Max(int $moduleLevel6Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_6_max = $moduleLevel6Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel7(): int
    {
        return $this->module_level_7;
    }

    #[Override]
    public function setModuleLevel7(int $moduleLevel7): ShipRumpModuleLevelInterface
    {
        $this->module_level_7 = $moduleLevel7;

        return $this;
    }

    #[Override]
    public function getModuleMandatory7(): bool
    {
        return $this->module_mandatory_7 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory7(int $moduleMandatory7): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_7 = $moduleMandatory7;

        return $this;
    }

    #[Override]
    public function getModuleLevel7Min(): int
    {
        return $this->module_level_7_min;
    }

    #[Override]
    public function setModuleLevel7Min(int $moduleLevel7Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_7_min = $moduleLevel7Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel7Max(): int
    {
        return $this->module_level_7_max;
    }

    #[Override]
    public function setModuleLevel7Max(int $moduleLevel7Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_7_max = $moduleLevel7Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel8(): int
    {
        return $this->module_level_8;
    }

    #[Override]
    public function setModuleLevel8(int $moduleLevel8): ShipRumpModuleLevelInterface
    {
        $this->module_level_8 = $moduleLevel8;

        return $this;
    }

    #[Override]
    public function getModuleMandatory8(): bool
    {
        return $this->module_mandatory_8 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory8(int $moduleMandatory8): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_8 = $moduleMandatory8;

        return $this;
    }

    #[Override]
    public function getModuleLevel8Min(): int
    {
        return $this->module_level_8_min;
    }

    #[Override]
    public function setModuleLevel8Min(int $moduleLevel8Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_8_min = $moduleLevel8Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel8Max(): int
    {
        return $this->module_level_8_max;
    }

    #[Override]
    public function setModuleLevel8Max(int $moduleLevel8Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_8_max = $moduleLevel8Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel9(): int
    {
        throw new RuntimeException('this should not be called!');
    }

    #[Override]
    public function getModuleMandatory9(): bool
    {
        throw new RuntimeException('this should not be called!');
    }

    #[Override]
    public function getModuleLevel9Min(): int
    {
        throw new RuntimeException('this should not be called!');
    }

    #[Override]
    public function getModuleLevel9Max(): int
    {
        throw new RuntimeException('this should not be called!');
    }

    #[Override]
    public function getModuleLevel10(): int
    {
        return $this->module_level_10;
    }

    #[Override]
    public function setModuleLevel10(int $moduleLevel10): ShipRumpModuleLevelInterface
    {
        $this->module_level_10 = $moduleLevel10;

        return $this;
    }

    #[Override]
    public function getModuleMandatory10(): bool
    {
        return $this->module_mandatory_10 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory10(int $moduleMandatory10): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_10 = $moduleMandatory10;

        return $this;
    }

    #[Override]
    public function getModuleLevel10Min(): int
    {
        return $this->module_level_10_min;
    }

    #[Override]
    public function setModuleLevel10Min(int $moduleLevel10Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_10_min = $moduleLevel10Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel10Max(): int
    {
        return $this->module_level_10_max;
    }

    #[Override]
    public function setModuleLevel10Max(int $moduleLevel10Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_10_max = $moduleLevel10Max;

        return $this;
    }

    #[Override]
    public function getModuleLevel11(): int
    {
        return $this->module_level_11;
    }

    #[Override]
    public function setModuleLevel11(int $moduleLevel11): ShipRumpModuleLevelInterface
    {
        $this->module_level_11 = $moduleLevel11;

        return $this;
    }

    #[Override]
    public function getModuleMandatory11(): bool
    {
        return $this->module_mandatory_11 === self::MODULE_MANDATORY;
    }

    #[Override]
    public function setModuleMandatory11(int $moduleMandatory11): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_11 = $moduleMandatory11;

        return $this;
    }

    #[Override]
    public function getModuleLevel11Min(): int
    {
        return $this->module_level_11_min;
    }

    #[Override]
    public function setModuleLevel11Min(int $moduleLevel11Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_11_min = $moduleLevel11Min;

        return $this;
    }

    #[Override]
    public function getModuleLevel11Max(): int
    {
        return $this->module_level_11_max;
    }

    #[Override]
    public function setModuleLevel11Max(int $moduleLevel11Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_11_max = $moduleLevel11Max;

        return $this;
    }


    #[Override]
    public function getMandatoryModulesCount(): int
    {
        return array_reduce(
            array_map(
                fn(SpacecraftModuleTypeEnum $type): string =>
                sprintf('getModuleMandatory%d', $type->value),
                array_filter(SpacecraftModuleTypeEnum::cases(), fn(SpacecraftModuleTypeEnum $type): bool => !$type->isSpecialSystemType())
            ),
            fn(int $value, string $method): int => $value + $this->$method() ? 1 : 0,
            0
        );
    }
}
