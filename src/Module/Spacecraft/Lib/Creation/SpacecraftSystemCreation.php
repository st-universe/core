<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\ModuleSpecialRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class SpacecraftSystemCreation implements SpacecraftSystemCreationInterface
{
    public function __construct(
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private ModuleSpecialRepositoryInterface $moduleSpecialRepository
    ) {}

    public function createShipSystemsByModuleList(
        SpacecraftInterface $spacecraft,
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


    /** @return array<int, ModuleInterface|null>  */
    private function getDefaultSystems(SpacecraftInterface $spacecraft): array
    {
        $systems = [];

        //default systems, that almost every ship should have
        if ($spacecraft->getRump()->getCategoryId() !== SpacecraftRumpEnum::SHIP_CATEGORY_SHUTTLE) {
            $systems[SpacecraftSystemTypeEnum::DEFLECTOR->value] = null;
            $systems[SpacecraftSystemTypeEnum::TRACTOR_BEAM->value] = null;
        }
        $systems[SpacecraftSystemTypeEnum::LIFE_SUPPORT->value] = null;
        //TODO transporter

        if ($spacecraft->getRump()->getCategoryId() === SpacecraftRumpEnum::SHIP_CATEGORY_STATION) {
            $systems[SpacecraftSystemTypeEnum::BEAM_BLOCKER->value] = null;
        }

        if ($spacecraft->getRump()->isShipyard()) {
            $systems[SpacecraftSystemTypeEnum::CONSTRUCTION_HUB->value] = null;
        }

        if ($spacecraft->getRump()->getRoleId() === SpacecraftRumpEnum::SHIP_ROLE_SENSOR) {
            $systems[SpacecraftSystemTypeEnum::UPLINK->value] = null;
        }

        return $systems;
    }

    private function createShipSystem(int $systemType, SpacecraftInterface $spacecraft, ?ModuleInterface $module): void
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
     * @param Collection<int, BuildplanModuleInterface> $buildplanModules
     * @param array<int, ModuleInterface|null> $systems
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

            switch ($module->getType()) {
                case SpacecraftModuleTypeEnum::SENSOR:
                    $systems[SpacecraftSystemTypeEnum::NBS->value] = null;
                    break;
            }
        }
    }

    /**
     * @param array<int, ModuleInterface|null> $systems
     */
    private function addSpecialSystems(SpacecraftCreationConfigInterface $specialSystemsProvider, array &$systems): void
    {
        foreach ($specialSystemsProvider->getSpecialSystemModules() as $module) {
            $this->addSpecialSystem($module, $systems);
        }
    }

    /**
     * @param array<int, null|ModuleInterface> $systems
     */
    private function addSpecialSystem(ModuleInterface $module, array &$systems): void
    {
        $moduleSpecials = $this->moduleSpecialRepository->getByModule($module->getId());

        foreach ($moduleSpecials as $special) {

            $moduleSpecial = $special->getSpecialId();
            $systems[$moduleSpecial->getSystemType()->value] = $moduleSpecial->hasCorrespondingModule() ? $module : null;
        }
    }
}
