<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use RuntimeException;
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
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

abstract class AbstractWeaponPhase
{
    protected LoggerUtilInterface $loggerUtil;

    public function __construct(
        private UserRepositoryInterface $userRepository,
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

    protected function getUser(int $userId): UserInterface
    {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new RuntimeException('this should not happen');
        }

        return $user;
    }
}
