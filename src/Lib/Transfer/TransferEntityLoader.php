<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Override;
use RuntimeException;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperFactoryInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TrumfieldInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\TrumfieldRepositoryInterface;

class TransferEntityLoader implements TransferEntityLoaderInterface
{
    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader*/
    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private TrumfieldRepositoryInterface $trumfieldRepository,
        private StorageEntityWrapperFactoryInterface $storageEntityWrapperFactory
    ) {}

    #[Override]
    public function loadEntity(
        int $id,
        TransferEntityTypeEnum $entityType,
        bool $checkForEntityLock = true,
        ?UserInterface $user = null
    ): StorageEntityWrapperInterface {


        //TODO add ?UserInterface parameter, and then do with ownerValidation
        /** @var null|SpacecraftInterface|ColonyInterface|TrumfieldInterface */
        $target = match ($entityType) {
            TransferEntityTypeEnum::SHIP,
            TransferEntityTypeEnum::STATION => $this->loadSpacecraft($id, $checkForEntityLock, $user),
            TransferEntityTypeEnum::COLONY => $this->loadColony($id, $checkForEntityLock, $user),
            TransferEntityTypeEnum::TRUMFIELD => $this->trumfieldRepository->find($id)
        };

        if ($target === null) {
            throw new RuntimeException(sprintf(
                'entity with id %d and type %d does not exist',
                $id,
                $entityType->value
            ));
        }

        return $this->storageEntityWrapperFactory->wrapStorageEntity($target);
    }

    private function loadSpacecraft(int $id, bool $checkForEntityLock, ?UserInterface $user): ?SpacecraftInterface
    {
        $spacecraftWrapper = $user !== null
            ? $this->spacecraftLoader->getWrapperByIdAndUser($id, $user->getId(), false, $checkForEntityLock)
            : $this->spacecraftLoader->find($id, $checkForEntityLock);

        return $spacecraftWrapper !== null
            ? $spacecraftWrapper->get()
            : null;
    }

    private function loadColony(int $id, bool $checkForEntityLock, ?UserInterface $user): ColonyInterface
    {
        return $user !== null
            ? $this->colonyLoader->loadWithOwnerValidation($id, $user->getId(), $checkForEntityLock)
            : $this->colonyLoader->load($id, $checkForEntityLock);
    }
}
