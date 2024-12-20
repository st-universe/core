<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * @template T of SpacecraftWrapperInterface
 * 
 * @implements SpacecraftCreatorInterface<T>
 */
final class SpacecraftCreator implements SpacecraftCreatorInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private UserRepositoryInterface $userRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private SpacecraftSystemCreationInterface $spacecraftSystemCreation,
        private SpacecraftFactoryInterface $spacecraftFactory,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private SpacecraftConfiguratorFactoryInterface $spacecraftConfiguratorFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function createBy(
        int $userId,
        int $rumpId,
        int $buildplanId,
        SpacecraftCreationConfigInterface $specialSystemsProvider
    ): SpacecraftConfiguratorInterface {

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new RuntimeException('user not existent');
        }

        $rump = $this->spacecraftRumpRepository->find($rumpId);
        if ($rump === null) {
            throw new RuntimeException('rump not existent');
        }

        $buildplan = $this->spacecraftBuildplanRepository->find($buildplanId);
        if ($buildplan === null) {
            throw new RuntimeException('buildplan not existent');
        }

        $spacecraft =  $specialSystemsProvider->getSpacecraft() ?? $this->spacecraftFactory->create($rump);
        $spacecraft->setUser($user);
        $spacecraft->setBuildplan($buildplan);
        $spacecraft->setRump($rump);
        $spacecraft->setState(SpacecraftStateEnum::SHIP_STATE_NONE);

        //create ship systems
        $this->spacecraftSystemCreation->createShipSystemsByModuleList(
            $spacecraft,
            $buildplan->getModules(),
            $specialSystemsProvider
        );

        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

        foreach (SpacecraftModuleTypeEnum::cases() as $moduleType) {

            $moduleTypeId = $moduleType->value;

            if ($this->loggerUtil->doLog()) {
                $this->loggerUtil->log(sprintf("moduleTypeId: %d", $moduleTypeId));
            }
            $buildplanModules = $buildplan->getModulesByType($moduleType);
            if (!$buildplanModules->isEmpty()) {
                if ($this->loggerUtil->doLog()) {
                    $this->loggerUtil->log("wrapperCallable!");
                }
                $moduleRumpWrapper = $moduleType->getModuleRumpWrapperCallable()($rump, $buildplan);
                $moduleRumpWrapper->apply($wrapper);
            }
        }

        if ($spacecraft->getName() === '' || $spacecraft->getName() === sprintf('%s in Bau', $spacecraft->getRump()->getName())) {
            $spacecraft->setName($spacecraft->getRump()->getName());
        }

        $spacecraft->setAlertStateGreen();

        $this->spacecraftRepository->save($spacecraft);

        return $this->spacecraftConfiguratorFactory->createSpacecraftConfigurator($wrapper);
    }
}
