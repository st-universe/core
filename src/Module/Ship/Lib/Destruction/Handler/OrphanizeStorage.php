<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class OrphanizeStorage implements ShipDestructionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private StorageRepositoryInterface $storageRepository
    ) {
    }

    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        foreach ($destroyedShipWrapper->get()->getStorage() as $storage) {
            $storage->setUser($this->userRepository->getFallbackUser());
            $this->storageRepository->save($storage);
        }
    }
}
