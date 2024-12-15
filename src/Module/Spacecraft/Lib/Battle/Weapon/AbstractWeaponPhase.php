<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftInterface;

abstract class AbstractWeaponPhase
{
    protected LoggerUtilInterface $loggerUtil;

    public function __construct(
        protected EntryCreatorInterface $entryCreator,
        protected ApplyDamageInterface $applyDamage,
        protected BuildingManagerInterface $buildingManager,
        protected StuRandom $stuRandom,
        protected MessageFactoryInterface $messageFactory,
        private SpacecraftDestructionInterface $spacecraftDestruction,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    protected function checkForSpacecraftDestruction(
        SpacecraftDestroyerInterface $attacker,
        SpacecraftWrapperInterface $targetWrapper,
        SpacecraftDestructionCauseEnum $destructionCause,
        InformationInterface $message
    ): void {

        if (!$targetWrapper->get()->isDestroyed()) {
            return;
        }

        $this->spacecraftDestruction->destroy(
            $attacker,
            $targetWrapper,
            $destructionCause,
            $message
        );
    }

    protected function getModule(SpacecraftInterface $ship, SpacecraftModuleTypeEnum $moduleType): ?ModuleInterface
    {
        $buildplan = $ship->getBuildplan();
        if ($buildplan === null) {
            return null;
        }

        $modules = $buildplan->getModulesByType($moduleType);
        if ($modules->isEmpty()) {
            return null;
        }

        return $modules->first();
    }
}
