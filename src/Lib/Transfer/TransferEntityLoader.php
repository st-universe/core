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
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Entity\User;
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
        ?User $user = null
    ): StorageEntityWrapperInterface {


        /** @var null|Spacecraft|Colony|Trumfield */
        $target = match ($entityType) {
            TransferEntityTypeEnum::SHIP,
            TransferEntityTypeEnum::STATION => $this->loadSpacecraft($id, $checkForEntityLock, $user),
            TransferEntityTypeEnum::COLONY => $this->loadColony($id, $checkForEntityLock, $user),
            TransferEntityTypeEnum::TRUMFIELD => $this->trumfieldRepository->find($id)
        };

        if ($target === null) {
            throw new TransferEntityNotFoundException(sprintf(
                'entity with id %d and type %s does not exist',
                $id,
                $entityType->value
            ));
        }

        return $this->storageEntityWrapperFactory->wrapStorageEntity($target);
    }

    private function loadSpacecraft(int $id, bool $checkForEntityLock, ?User $user): ?Spacecraft
    {
        $spacecraftWrapper = $user !== null
            ? $this->spacecraftLoader->getWrapperByIdAndUser($id, $user->getId(), false, $checkForEntityLock)
            : $this->spacecraftLoader->find($id, $checkForEntityLock);

        return $spacecraftWrapper !== null
            ? $spacecraftWrapper->get()
            : null;
    }

    private function loadColony(int $id, bool $checkForEntityLock, ?User $user): Colony
    {
        return $user !== null
            ? $this->colonyLoader->loadWithOwnerValidation($id, $user->getId(), $checkForEntityLock)
            : $this->colonyLoader->load($id, $checkForEntityLock);
    }
}
