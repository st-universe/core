<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\TholianWebInterface;

class LeaveIntactModules implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private StorageManagerInterface $storageManager
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $spacecraft = $destroyedSpacecraftWrapper->get();

        if ($spacecraft->isShuttle() || $spacecraft instanceof TholianWebInterface) {
            return;
        }

        $intactModules = [];

        foreach ($spacecraft->getSystems() as $system) {
            if (
                $system->getModule() !== null
                && $system->getStatus() == 100
            ) {
                $module = $system->getModule();

                if (!array_key_exists($module->getId(), $intactModules)) {
                    $intactModules[$module->getId()] = $module;
                }
            }
        }

        //leave 50% of all intact modules
        $leaveCount = (int) ceil(count($intactModules) / 2);

        //maximum of 1 if ship is pirate
        if ($spacecraft->getUser()->getId() === UserEnum::USER_NPC_KAZON) {
            $leaveCount = min(1, $leaveCount);
        }

        for ($i = 1; $i <= $leaveCount; $i++) {
            $module = $intactModules[array_rand($intactModules)];
            unset($intactModules[$module->getId()]);

            $this->storageManager->upperStorage(
                $spacecraft,
                $module->getCommodity(),
                1
            );
        }
    }
}
