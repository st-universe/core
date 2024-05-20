<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class LeaveIntactModules implements ShipDestructionHandlerInterface
{
    public function __construct(
        private ShipStorageManagerInterface $shipStorageManager
    ) {
    }

    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $ship = $destroyedShipWrapper->get();

        if ($ship->isShuttle()) {
            return;
        }

        $intactModules = [];

        foreach ($ship->getSystems() as $system) {
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
        if ($ship->getUser()->getId() === UserEnum::USER_NPC_KAZON) {
            $leaveCount = min(1, $leaveCount);
        }

        for ($i = 1; $i <= $leaveCount; $i++) {
            $module = $intactModules[array_rand($intactModules)];
            unset($intactModules[$module->getId()]);

            $this->shipStorageManager->upperStorage(
                $ship,
                $module->getCommodity(),
                1
            );
        }
    }
}
