<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

class SystemDeactivationHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly CrewAssignmentRepositoryInterface $crewAssignmentRepository
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        $spacecraft = $wrapper->get();
        $hasEnoughCrew = $this->crewAssignmentRepository->hasEnoughCrew($spacecraft);

        // not enough crew
        if (!$hasEnoughCrew) {
            $information->addInformation('Zu wenig Crew an Bord, Schiff ist nicht voll funktionsfähig! Systeme werden deaktiviert!');

            //deactivate all systems except life support
            foreach ($this->spacecraftSystemManager->getActiveSystems($spacecraft) as $system) {
                if ($system->getSystemType() != SpacecraftSystemTypeEnum::LIFE_SUPPORT) {
                    $this->spacecraftSystemManager->deactivate($wrapper, $system->getSystemType(), true);
                }
            }
        }
    }
}
