<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Module;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class ModuleRecycling implements ModuleRecyclingInterface
{
    public function __construct(
        private StorageManagerInterface $storageManager,
        private StuRandom $stuRandom
    ) {}

    #[Override]
    public function retrieveSomeModules(
        SpacecraftInterface $spacecraft,
        EntityWithStorageInterface $entity,
        InformationInterface $information,
        int $recyclingChance = 50
    ): void {

        /** @var array<int, array{0: ModuleInterface, 1: int, 2: int}> */
        $recycledModuleChances = [];

        $buildplan = $spacecraft->getBuildplan();
        if ($buildplan === null) {
            return;
        }

        $buildplanModules = $buildplan->getModules();

        // recycle installed systems based on health
        foreach ($spacecraft->getSystems() as $system) {

            $module = $system->getModule();
            if ($module !== null) {
                if (!array_key_exists($module->getId(), $recycledModuleChances)) {

                    $buildplanModule = $buildplanModules->get($module->getId());
                    $amount = $buildplanModule === null ? 1 : $buildplanModule->getModuleCount();
                    $chance = (int)ceil($recyclingChance * $system->getStatus() / 100);

                    $recycledModuleChances[$module->getId()] = [$module, $amount, $chance];
                }
            }
        }

        // recycle buildplan modules
        foreach ($buildplanModules as $buildplanModule) {

            $module = $buildplanModule->getModule();
            if (!array_key_exists($module->getId(), $recycledModuleChances)) {
                $moduleCount = $buildplanModule->getModuleCount();

                $recycledModuleChances[$module->getId()] = [
                    $module,
                    $this->stuRandom->rand(1, $moduleCount, true, (int)ceil($moduleCount / 2)),
                    intdiv($recyclingChance, 2)
                ];
            }
        }

        $maxStorage = $entity->getMaxStorage();

        foreach ($recycledModuleChances as [$module, $amount, $recyclingChance]) {

            if ($entity->getStorageSum() >= $maxStorage) {
                $information->addInformation('Kein Lagerraum frei um Module zu recyclen!');
                break;
            }

            if ($this->stuRandom->rand(1, 100) > $recyclingChance) {
                continue;
            }

            $this->storageManager->upperStorage(
                $entity,
                $module->getCommodity(),
                $amount
            );

            $information->addInformationf('Folgendes Modul konnte recycelt werden: %s, Anzahl: %d', $module->getName(), $amount);
        }
    }
}
