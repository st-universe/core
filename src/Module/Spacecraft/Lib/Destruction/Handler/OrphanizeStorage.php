<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class OrphanizeStorage implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private StorageRepositoryInterface $storageRepository
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        foreach ($destroyedSpacecraftWrapper->get()->getStorage() as $storage) {
            $storage->setUser($this->userRepository->getFallbackUser());
            $this->storageRepository->save($storage);
        }
    }
}
