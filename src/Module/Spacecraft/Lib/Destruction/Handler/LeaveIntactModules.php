<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\TholianWeb;

class LeaveIntactModules implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private readonly StorageManagerInterface $storageManager,
        private readonly StuRandom $stuRandom
    ) {}

    #[\Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $spacecraft = $destroyedSpacecraftWrapper->get();

        if ($spacecraft->isShuttle() || $spacecraft instanceof TholianWeb) {
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
        if ($spacecraft->getUser()->getId() === UserConstants::USER_NPC_KAZON) {
            $leaveCount = min(1, $leaveCount);
        }

        for ($i = 1; $i <= $leaveCount; $i++) {
            $module = $intactModules[$this->stuRandom->array_rand($intactModules)];
            unset($intactModules[$module->getId()]);

            $this->storageManager->upperStorage(
                $spacecraft,
                $module->getCommodity(),
                1
            );
        }
    }
}
