<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\ModuleSpecialRepositoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;

final class ShipRetrofit implements ShipRetrofitInterface
{
    public function __construct(
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private ModuleSpecialRepositoryInterface $moduleSpecialRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private StorageManagerInterface $storageManager,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function updateBy(ShipInterface $ship, SpacecraftBuildplanInterface $newBuildplan, ColonyInterface $colony): void
    {
        $oldBuildplan = $ship->getBuildplan();
        $wrapper = $this->spacecraftWrapperFactory->wrapShip($ship);

        if ($oldBuildplan === null) {
            return;
        }

        $returnedmodules = [];

        foreach (SpacecraftModuleTypeEnum::getModuleSelectorOrder() as $moduleType) {

            $oldModules = $oldBuildplan->getModulesByType($moduleType)->toArray();
            $newModules = $newBuildplan->getModulesByType($moduleType)->toArray();

            /** @var array<ModuleInterface> */
            $addingModules = array_udiff($newModules, $oldModules, function (ModuleInterface $a, ModuleInterface $b): int {
                return $a->getId() - $b->getId();
            });

            /** @var array<ModuleInterface> */
            $deletingModules = array_udiff($oldModules, $newModules, function (ModuleInterface $a, ModuleInterface $b): int {
                return $a->getId() - $b->getId();
            });

            foreach ($deletingModules as $oldModule) {
                $this->removeModule($ship, $oldModule, $returnedmodules);
            }

            if ($addingModules !== []) {
                $systems = [];
                $this->addModuleSystems($addingModules, $systems);
                foreach ($systems as $systemType => $module) {
                    $this->createShipSystem($systemType, $ship, $module);
                    $moduleRumpWrapper = $moduleType->getModuleRumpWrapperCallable()($ship->getRump(), $newBuildplan);
                    $moduleRumpWrapper->apply($wrapper);
                }
            }
        }

        if ($returnedmodules !== []) {
            $msg = "
            Die folgenden Module wurden durch den Umbau zurückgewonnen: ";
            foreach ($returnedmodules as $module) {
                if ($module != null) {
                    $this->storageManager->upperStorage($colony, $module->getCommodity(), 1);
                    $msg .= $module->getName() . ", ";
                }
            }
            $msg = rtrim($msg, ", ");
        } else {
            $msg = null;
        }

        $txt = _("Auf der Kolonie " . $colony->getName() . " wurde die " . $ship->getName() . " umgerüstet");

        if ($msg !== null) {
            $txt .= '. ' . $msg;
        }

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $colony->getUserId(),
            $txt,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY
        );

        $ship->setBuildplan($newBuildplan);
    }

    /** @param array<ModuleInterface> $returnedmodules */
    private function removeModule(ShipInterface $ship, ModuleInterface $oldModule, array &$returnedmodules): void
    {
        if ($oldModule->getType() != SpacecraftModuleTypeEnum::HULL) {

            $system = $this->getSystemByModule($oldModule, $ship);
            if ($system->getStatus() >= 100 && mt_rand(1, 100) <= 25) {
                $returnedmodules[] = $oldModule;
            }
            $this->shipSystemRepository->delete($system);
            $ship->getSystems()->removeElement($system);
        }
    }

    private function getSystemByModule(ModuleInterface $module, ShipInterface $ship): SpacecraftSystemInterface
    {
        foreach ($ship->getSystems()  as $system) {
            if ($system->getModule() === $module) {
                return $system;
            }
        }

        throw new RuntimeException(sprintf('no system installed with moduleId: %d', $module->getId()));
    }

    /**
     * @param array<ModuleInterface> $modules
     * @param array<int, ModuleInterface|null> $systems
     */
    private function addModuleSystems(array $modules, array &$systems): void
    {
        foreach ($modules as $module) {

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

            if ($module->getType() === SpacecraftModuleTypeEnum::SPECIAL) {
                $this->addSpecialSystems($module, $systems);
            }
        }
    }

    /**
     * @param array<int, null|ModuleInterface> $systems
     */
    private function addSpecialSystems(ModuleInterface $module, array &$systems): void
    {
        $moduleSpecials = $this->moduleSpecialRepository->getByModule($module->getId());

        foreach ($moduleSpecials as $special) {
            $moduleSpecial = $special->getSpecialId();
            $systems[$moduleSpecial->getSystemType()->value] = $moduleSpecial->hasCorrespondingModule() ? $module : null;
        }
    }


    private function createShipSystem(int $systemType, ShipInterface $ship, ?ModuleInterface $module): void
    {
        $shipSystem = $this->shipSystemRepository
            ->prototype()
            ->setSpacecraft($ship)
            ->setSystemType(SpacecraftSystemTypeEnum::from($systemType))
            ->setStatus(100)
            ->setMode(SpacecraftSystemModeEnum::MODE_OFF);

        if ($module !== null) {
            $shipSystem->setModule($module);
        }

        $this->shipSystemRepository->save($shipSystem);

        $ship->getSystems()->set($systemType, $shipSystem);
    }
}