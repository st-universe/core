<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Module;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\SpacecraftSystem;

final class ShipRetrofit implements ShipRetrofitInterface
{
    public function __construct(
        private readonly SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly StorageManagerInterface $storageManager,
        private readonly PrivateMessageSenderInterface $privateMessageSender,
        private readonly StuRandom $stuRandom
    ) {}

    #[Override]
    public function updateBy(Ship $ship, SpacecraftBuildplan $newBuildplan, Colony $colony): void
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

            /** @var array<Module> */
            $addingModules = array_udiff($newModules, $oldModules, function (Module $a, Module $b): int {
                return $a->getId() - $b->getId();
            });

            /** @var array<Module> */
            $deletingModules = array_udiff($oldModules, $newModules, function (Module $a, Module $b): int {
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
            UserConstants::USER_NOONE,
            $colony->getUserId(),
            $txt,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY
        );

        $ship->setBuildplan($newBuildplan);
    }

    /** @param array<Module> $returnedmodules */
    private function removeModule(Ship $ship, Module $oldModule, array &$returnedmodules): void
    {
        if ($oldModule->getType() != SpacecraftModuleTypeEnum::HULL) {

            $system = $this->getSystemByModule($oldModule, $ship);
            if ($system->getStatus() >= 100 && $this->stuRandom->rand(1, 100) <= 25) {
                $returnedmodules[] = $oldModule;
            }
            $this->shipSystemRepository->delete($system);
            $ship->getSystems()->removeElement($system);
        }
    }

    private function getSystemByModule(Module $module, Ship $ship): SpacecraftSystem
    {
        foreach ($ship->getSystems()  as $system) {
            if ($system->getModule() === $module) {
                return $system;
            }
        }

        throw new RuntimeException(sprintf('no system installed with moduleId: %d', $module->getId()));
    }

    /**
     * @param array<Module> $modules
     * @param array<int, Module|null> $systems
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
     * @param array<int, null|Module> $systems
     */
    private function addSpecialSystems(Module $module, array &$systems): void
    {
        foreach ($module->getSpecials() as $special) {
            $moduleSpecial = $special->getSpecialId();
            $systemType = $moduleSpecial->getSystemType();

            if ($systemType !== null) {
                $systems[$systemType->value] = $moduleSpecial->hasCorrespondingModule() ? $module : null;
            }
        }
    }

    private function createShipSystem(int $systemType, Ship $ship, ?Module $module): void
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
