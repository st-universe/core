<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

interface ShipRumpModuleLevelInterface
{
    public const MODULE_MANDATORY = 1;

    public function getId(): int;

    public function getRumpId(): int;

    public function getModuleLevel1(): int;

    public function setModuleLevel1(int $moduleLevel1): ShipRumpModuleLevelInterface;

    public function getModuleMandatory1(): bool;

    public function setModuleMandatory1(int $moduleMandatory1): ShipRumpModuleLevelInterface;

    public function getModuleLevel1Min(): int;

    public function setModuleLevel1Min(int $moduleLevel1Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel1Max(): int;

    public function setModuleLevel1Max(int $moduleLevel1Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel2(): int;

    public function setModuleLevel2(int $moduleLevel2): ShipRumpModuleLevelInterface;

    public function getModuleMandatory2(): bool;

    public function setModuleMandatory2(int $moduleMandatory2): ShipRumpModuleLevelInterface;

    public function getModuleLevel2Min(): int;

    public function setModuleLevel2Min(int $moduleLevel2Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel2Max(): int;

    public function setModuleLevel2Max(int $moduleLevel2Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel3(): int;

    public function setModuleLevel3(int $moduleLevel3): ShipRumpModuleLevelInterface;

    public function getModuleMandatory3(): bool;

    public function setModuleMandatory3(int $moduleMandatory3): ShipRumpModuleLevelInterface;

    public function getModuleLevel3Min(): int;

    public function setModuleLevel3Min(int $moduleLevel3Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel3Max(): int;

    public function setModuleLevel3Max(int $moduleLevel3Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel4(): int;

    public function setModuleLevel4(int $moduleLevel4): ShipRumpModuleLevelInterface;

    public function getModuleMandatory4(): bool;

    public function setModuleMandatory4(int $moduleMandatory4): ShipRumpModuleLevelInterface;

    public function getModuleLevel4Min(): int;

    public function setModuleLevel4Min(int $moduleLevel4Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel4Max(): int;

    public function setModuleLevel4Max(int $moduleLevel4Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel5(): int;

    public function setModuleLevel5(int $moduleLevel5): ShipRumpModuleLevelInterface;

    public function getModuleMandatory5(): bool;

    public function setModuleMandatory5(int $moduleMandatory5): ShipRumpModuleLevelInterface;

    public function getModuleLevel5Min(): int;

    public function setModuleLevel5Min(int $moduleLevel5Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel5Max(): int;

    public function setModuleLevel5Max(int $moduleLevel5Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel6(): int;

    public function setModuleLevel6(int $moduleLevel6): ShipRumpModuleLevelInterface;

    public function getModuleMandatory6(): bool;

    public function setModuleMandatory6(int $moduleMandatory6): ShipRumpModuleLevelInterface;

    public function getModuleLevel6Min(): int;

    public function setModuleLevel6Min(int $moduleLevel6Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel6Max(): int;

    public function setModuleLevel6Max(int $moduleLevel6Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel7(): int;

    public function setModuleLevel7(int $moduleLevel7): ShipRumpModuleLevelInterface;

    public function getModuleMandatory7(): bool;

    public function setModuleMandatory7(int $moduleMandatory7): ShipRumpModuleLevelInterface;

    public function getModuleLevel7Min(): int;

    public function setModuleLevel7Min(int $moduleLevel7Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel7Max(): int;

    public function setModuleLevel7Max(int $moduleLevel7Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel8(): int;

    public function setModuleLevel8(int $moduleLevel8): ShipRumpModuleLevelInterface;

    public function getModuleMandatory8(): bool;

    public function setModuleMandatory8(int $moduleMandatory8): ShipRumpModuleLevelInterface;

    public function getModuleLevel8Min(): int;

    public function setModuleLevel8Min(int $moduleLevel8Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel8Max(): int;

    public function setModuleLevel8Max(int $moduleLevel8Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel9(): int;

    public function getModuleMandatory9(): bool;

    public function getModuleLevel9Min(): int;

    public function getModuleLevel9Max(): int;

    public function getModuleLevel10(): int;

    public function setModuleLevel10(int $moduleLevel10): ShipRumpModuleLevelInterface;

    public function getModuleMandatory10(): bool;

    public function setModuleMandatory10(int $moduleMandatory10): ShipRumpModuleLevelInterface;

    public function getModuleLevel10Min(): int;

    public function setModuleLevel10Min(int $moduleLevel10Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel10Max(): int;

    public function setModuleLevel10Max(int $moduleLevel10Max): ShipRumpModuleLevelInterface;

    public function getModuleLevel11(): int;

    public function setModuleLevel11(int $moduleLevel11): ShipRumpModuleLevelInterface;

    public function getModuleMandatory11(): bool;

    public function setModuleMandatory11(int $moduleMandatory11): ShipRumpModuleLevelInterface;

    public function getModuleLevel11Min(): int;

    public function setModuleLevel11Min(int $moduleLevel11Min): ShipRumpModuleLevelInterface;

    public function getModuleLevel11Max(): int;

    public function setModuleLevel11Max(int $moduleLevel11Max): ShipRumpModuleLevelInterface;

    public function getMandatoryModulesCount(): int;
}
