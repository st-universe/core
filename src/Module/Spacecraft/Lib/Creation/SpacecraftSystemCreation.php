<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class SpacecraftSystemCreation implements SpacecraftSystemCreationInterface
{
    public function __construct(
        private SpacecraftSystemRepositoryInterface $shipSystemRepository
    ) {}

    public function createShipSystemsByModuleList(
        Spacecraft $spacecraft,
        Collection $buildplanModules,
        ?SpacecraftCreationConfigInterface $spacecraftCreationConfig
    ): void {
        $systems = $this->getDefaultSystems($spacecraft);

        $this->addModuleSystems($buildplanModules, $systems);

        if ($spacecraftCreationConfig !== null) {
            $this->addSpecialSystems($spacecraftCreationConfig, $systems);
        }

        foreach ($systems as $systemType => $module) {
            $this->createShipSystem($systemType, $spacecraft, $module);
        }
    }


    /** @return array<int, Module|null>  */
    private function getDefaultSystems(Spacecraft $spacecraft): array
    {
        $systems = [];

        //default systems, that almost every ship should have
        if ($spacecraft->getRump()->getCategoryId() !== SpacecraftRumpCategoryEnum::SHIP_CATEGORY_SHUTTLE) {
            $systems[SpacecraftSystemTypeEnum::DEFLECTOR->value] = null;
            $systems[SpacecraftSystemTypeEnum::TRACTOR_BEAM->value] = null;
        }
        $systems[SpacecraftSystemTypeEnum::LIFE_SUPPORT->value] = null;
        //TODO transporter

        if ($spacecraft->getRump()->getCategoryId() === SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION) {
            $systems[SpacecraftSystemTypeEnum::BEAM_BLOCKER->value] = null;
        }

        if ($spacecraft->getRump()->isShipyard()) {
            $systems[SpacecraftSystemTypeEnum::CONSTRUCTION_HUB->value] = null;
        }

        if ($spacecraft->getRump()->getRoleId() === SpacecraftRumpRoleEnum::SENSOR) {
            $systems[SpacecraftSystemTypeEnum::UPLINK->value] = null;
        }

        return $systems;
    }

    private function createShipSystem(int $systemType, Spacecraft $spacecraft, ?Module $module): void
    {
        $spacecraftSystem = $this->shipSystemRepository->prototype();
        $spacecraftSystem->setSpacecraft($spacecraft);
        $spacecraft->getSystems()->set($systemType, $spacecraftSystem);
        $spacecraftSystem->setSystemType(SpacecraftSystemTypeEnum::from($systemType));
        if ($module !== null) {
            $spacecraftSystem->setModule($module);
        }
        $spacecraftSystem->setStatus(100);
        $spacecraftSystem->setMode(SpacecraftSystemModeEnum::MODE_OFF);

        $this->shipSystemRepository->save($spacecraftSystem);
    }

    /**
     * @param Collection<int, BuildplanModule> $buildplanModules
     * @param array<int, Module|null> $systems
     */
    private function addModuleSystems(Collection $buildplanModules, array &$systems): void
    {
        foreach ($buildplanModules as $buildplanmodule) {
            $module = $buildplanmodule->getModule();

            $systemType = $module->getSystemType();
            if (
                $systemType === null
                && $module->getType()->hasCorrespondingSystemType()
            ) {
                $systemType = $module->getType()->getSystemType();
            }

            if ($systemType !== null) {
                $systems[$systemType->value] = $module;
            }

            if ($module->getType() === SpacecraftModuleTypeEnum::SENSOR) {
                $systems[SpacecraftSystemTypeEnum::NBS->value] = null;
            }
        }
    }

    /**
     * @param array<int, Module|null> $systems
     */
    private function addSpecialSystems(SpacecraftCreationConfigInterface $specialSystemsProvider, array &$systems): void
    {
        foreach ($specialSystemsProvider->getSpecialSystemModules() as $module) {
            $this->addSpecialSystem($module, $systems);
        }
    }

    /**
     * @param array<int, null|Module> $systems
     */
    private function addSpecialSystem(Module $module, array &$systems): void
    {
        foreach ($module->getSpecials() as $special) {
            $moduleSpecial = $special->getSpecialId();

            $systemType = $moduleSpecial->getSystemType();
            if ($systemType !== null) {
                $systems[$systemType->value] = $moduleSpecial->hasCorrespondingModule() ? $module : null;
            }
        }
    }
}
